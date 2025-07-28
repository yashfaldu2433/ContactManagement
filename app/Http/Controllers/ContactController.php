<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contacts\ContactRequest;
use App\Models\Media;
use App\Models\Contact;
use App\Models\Document;
use App\Services\UrlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\JsonResponseService;
use Illuminate\Support\Facades\Storage;

class ContactController extends Controller
{
    protected $jsonResponseService, $urlService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(JsonResponseService $jasonResponseService, UrlService $urlService)
    {
        $this->jsonResponseService = $jasonResponseService;
        $this->urlService = $urlService;
    }

    /**
     * Show Contact List Page with DataTable
     *
     */
    public function index()
    {
        try {
            $contacts = Contact::all();
            return view('contacts.list', compact('contacts'));
        } catch (\Exception $e) {
            // LOG ERROR MESSAGE
            Log::error(__METHOD__ . " | Error: {$e->getMessage()}");
            return $this->jsonResponseService->sendResponse(false, null, __('message.DEFAULT_ERROR_MESSAGE'), 500);
        }
    }

    /**
     * Fetches the list of contacts along with their profile image and documents.
     * @param \Illuminate\Http\Request $request  The incoming HTTP request instance.
     *
     * @return \Illuminate\Http\JsonResponse  JSON response containing contact data or an error message.
     */
    public function listAjax(Request $request)
    {
        try {
            $columns = [
                0 => 'first_name',
                1 => 'last_name',
                2 => 'email',
                3 => 'phone',
                4 => 'gender',
                5 => 'profile_image_id',
                6 => 'id',
            ];

            $search = $request->input('search.value');
            $order = $request->input('order')[0] ?? ['column' => 0, 'dir' => 'asc'];
            $orderColumn = $columns[$order['column']] ?? 'id';
            $orderDir = $order['dir'] ?? 'desc';

            $query = Contact::with(['profileImage', 'documents'])
                ->select('id', 'first_name', 'last_name', 'email', 'phone', 'gender', 'profile_image_id');

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            $totalData = Contact::count();
            $totalFiltered = $query->count();

            $contacts = $query
                ->orderBy($orderColumn, $orderDir)
                ->offset($request->input('start'))
                ->limit($request->input('length'))
                ->get();

            $data = [];
            foreach ($contacts as $contact) {
                $encodedId = $this->urlService->base64UrlEncode($contact->id);
                $data[] = [
                    'first_name' => $contact->first_name,
                    'last_name'  => $contact->last_name,
                    'email'      => $contact->email,
                    'phone'      => $contact->phone,
                    'gender'     => $contact->gender,
                    'profile_image' => $contact->profileImage
                        ? '<img src="' . asset('storage/' . $contact->profileImage->file_path) . '" width="40">'
                        : 'No Image',
                    'documents' => $contact->documents->count()
                        ? $contact->documents->map(
                            fn($doc) =>
                            "<a href='" . asset('storage/' . $doc->file_path) . "' target='_blank'>{$doc->file_name}</a>"
                        )->implode('<br>')
                        : 'No Documents',
                    'action' => "
                    <button class='btn btn-warning btn-sm edit-contact' data-id='{$encodedId}'>Edit</button>
                    <button class='btn btn-danger btn-sm delete-contact' data-id='{$encodedId}'>Delete</button>
                "
                ];
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalData,
                'recordsFiltered' => $totalFiltered,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error(__METHOD__ . " Error: " . $e->getMessage());
            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Something went wrong while fetching contacts.'
            ], 500);
        }
    }


    /**
     * Save Contact Data
     * @
     * @param  int  $contactId
     * 
     */
    public function saveContactData(ContactRequest $request)
    {
        try {
            DB::beginTransaction();
            $contact = Contact::updateOrCreate(
                ['id' => $request->id],
                $request->only(['first_name', 'last_name', 'email', 'phone', 'gender'])
            );

            if ($request->hasFile('profile_image')) {
                if ($contact->profileImage) {
                    Storage::disk('public')->delete($contact->profileImage->file_path);
                    $contact->profileImage->delete();
                }

                $file = $request->file('profile_image');
                $path = $file->store('uploads/profile_images', 'public');

                $media = Media::create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                ]);

                $contact->update(['profile_image_id' => $media->id]);
            }

            if ($request->hasFile('additional_files')) {
                if ($contact->documents()->count() > 0) {
                    foreach ($contact->documents as $doc) {
                        Storage::disk('public')->delete($doc->file_path);
                        $doc->delete();
                    }
                    $contact->documents()->detach();
                }

                foreach ($request->file('additional_files') as $file) {
                    $path = $file->store('uploads/documents', 'public');
                    $document = Document::create([
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                    ]);

                    $contact->documents()->attach($document->id);
                }
            }

            DB::commit();

            $message = $request->id ? 'Contact Updated Successfully.' : 'Contact Created Successfully.';
            return $this->jsonResponseService->sendResponse(true, null, $message, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error(__METHOD__ . " | Error: {$e->getMessage()}");
            return $this->jsonResponseService->sendResponse(false, null, __('message.DEFAULT_ERROR_MESSAGE'), 500);
        }
    }


    /**
     * Summary of create
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $id = $this->urlService->base64UrlDecode($id);
        $contact = Contact::with(['profileImage', 'documents'])->findOrFail($id);
        $contact->documents->transform(function ($doc) {
            $doc->encrypted_id = $this->urlService->base64UrlEncode($doc->id);
            return $doc;
        });
        return view('contacts.edit', compact('contact'));
    }



    public function create()
    {
        return view('contacts.edit');
    }

    /**
     * Delete Contact
     * @param  int  $id
     */
    public function delete($id)
    {
        try {
            $id = $this->urlService->base64UrlDecode($id);
            $contact = Contact::findOrFail($id);
            $contact->delete();
            return $this->jsonResponseService->sendResponse(true, null,  'Contact deleted successfully');
        } catch (\Exception $e) {
            return $this->jsonResponseService->sendResponse(false, null, __('message.DEFAULT_ERROR_MESSAGE'), 500);
        }
    }


    /**
     * Delete a document and detach it from contacts.
     *
     * @param int $id  Document ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteDocument($id)
    {
        try {
            $id = $this->urlService->base64UrlDecode($id);
            $document = Document::findOrFail($id);

            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            $document->contacts()->detach();
            $document->delete();

            return $this->jsonResponseService->sendResponse(true, null, 'Document deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->jsonResponseService->sendResponse(false, null, __('message.DEFAULT_ERROR_MESSAGE'), 500);
        }
    }
}
