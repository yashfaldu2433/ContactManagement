@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>{{ isset($contact) ? 'Edit Contact' : 'Create Contact' }}</h2>

        <form id="contactForm" enctype="multipart/form-data">
            @csrf

            <input type="hidden" name="id" value="{{ $contact->id ?? '' }}">

            <div class="mb-3">
                <label>First Name</label>
                <input type="text" name="first_name" class="form-control"
                    value="{{ old('first_name', isset($contact) ? $contact->first_name : '') }}" required>
                @error('first_name')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-3">
                <label>Last Name</label>
                <input type="text" name="last_name" class="form-control"
                    value="{{ old('last_name', isset($contact) ? $contact->last_name : '') }}" required>
                @error('last_name')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control"
                    value="{{ old('email', isset($contact) ? $contact->email : '') }}" required>
                @error('email')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-3">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control"
                    value="{{ old('phone', isset($contact) ? $contact->phone : '') }}" required>
                @error('phone')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-3">
                <label>Gender</label><br>
                <label>
                    <input type="radio" name="gender" value="Male" {{ old('gender', ucfirst(strtolower($contact->gender ?? ''))) == 'Male' ? 'checked' : '' }}> Male
                </label>
                &nbsp;
                <label>
                    <input type="radio" name="gender" value="Female" {{ old('gender', ucfirst(strtolower($contact->gender ?? ''))) == 'Female' ? 'checked' : '' }}> Female
                </label>
                &nbsp;
                <label>
                    <input type="radio" name="gender" value="Other" {{ old('gender', ucfirst(strtolower($contact->gender ?? ''))) == 'Other' ? 'checked' : '' }}> Other
                </label>
                @error('gender')
                    <br><small class="text-danger">{{ $message }}</small>
                @enderror
            </div>


            <div class="mb-3">
                <label>Profile Image</label><br>

                @if(isset($contact->profileImage) && $contact->profileImage->file_path)
                    <img src="{{ asset('storage/' . $contact->profileImage->file_path) }}" alt="Profile Image" width="100"
                        class="mb-2">
                @endif

                <input type="file" name="profile_image" class="form-control">
                @error('profile_image')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>



            <div class="mb-3">
                <label>Additional Files</label><br>

                @if(isset($contact->documents) && $contact->documents->count() > 0)
                    <ul>
                        @foreach($contact->documents as $doc)
                            <li>
                                <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank">{{ $doc->file_name }}</a>
                                <button type="button" class="btn btn-sm btn-danger delete-doc"
                                    data-id="{{ $doc->encrypted_id }}">Delete</button>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div id="file-inputs">
                    <div class="input-group mb-2">
                        <input type="file" name="additional_files[]" class="form-control">
                        <button type="button" class="btn btn-danger remove-file" style="display:none;">X</button>
                    </div>
                </div>
                <button type="button" id="add-file" class="btn btn-sm btn-primary">+ Add More</button>
                @error('additional_files')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>



            <button type="submit" class="btn btn-success">{{ isset($contact) ? 'Update' : 'Create' }}</button>
        </form>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {

            $("#add-file").click(function () {
                let newInput = `
                                    <div class="input-group mb-2">
                                        <input type="file" name="additional_files[]" class="form-control">
                                        <button type="button" class="btn btn-danger remove-file">X</button>
                                    </div>`;
                $("#file-inputs").append(newInput);
            });

            $(document).on("click", ".remove-file", function () {
                $(this).closest(".input-group").remove();
            });

            $('#contactForm').submit(function (e) {
                e.preventDefault();

                let formData = new FormData(this);
                let url = "{{ isset($contact) ? route('contacts.save', $contact->id) : route('contacts.save') }}";

                $.ajax({
                    url: url,
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (res) {
                        if (res.success) {
                            alert(res.message || "Contact saved successfully");
                            window.location.href = "{{ route('contacts.index') }}";
                        } else {
                            alert(res.message || "Something went wrong");
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            $('.text-danger').remove();
                            let errors = xhr.responseJSON.errors;
                            for (let field in errors) {
                                $(`[name="${field}"]`).after(`<small class="text-danger">${errors[field][0]}</small>`);
                            }
                        } else {
                            alert("Error: " + xhr.responseJSON.message);
                        }
                    }
                });
            });

            $(document).on("click", ".delete-doc", function () {
                if (!confirm("Are you sure you want to delete this file?")) return;

                let docId = $(this).data("id");
                let button = $(this);

                $.ajax({
                    url: "/contacts/documents/" + docId,
                    type: "DELETE",
                    data: { _token: "{{ csrf_token() }}" },
                    success: function (res) {
                        if (res.success) {
                            alert(res.message);
                            button.closest("li").remove();
                        } else {
                            alert("Something went wrong!");
                        }
                    },
                    error: function () {
                        alert("Error deleting file");
                    }
                });
            });

        });

    </script>
@endsection