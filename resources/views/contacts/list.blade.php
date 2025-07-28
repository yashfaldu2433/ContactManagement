@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Contact List</h2>
            <a href="{{ route('contacts.create') }}" class="btn btn-primary">Create Contact</a>
        </div>

        <table class="table table-bordered" id="contacts-table">
            <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Gender</th>
                    <th>Profile Image</th>
                    <th>Documents</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

    <script>
        $(function () {
            function dataTableInit(ajaxParams) {
                $('#contacts-table').DataTable({
                    searching: true,
                    responsive: true,
                    processing: true,
                    serverSide: true,
                    destroy: true,
                    scrollX: true,
                    order: [[0, "asc"]],
                    lengthMenu: [5, 10, 25, 50],
                    pageLength: 5,
                    ajax: {
                        url: "{{ route('contacts.listAjax') }}",
                        type: "GET",
                        headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                        data: ajaxParams
                    },
                    columns: [
                        { data: "first_name", className: "text-left" },
                        { data: "last_name", className: "text-left" },
                        { data: "email", className: "text-left" },
                        { data: "phone", className: "text-left" },
                        { data: "gender", className: "text-left" },
                        { data: "profile_image", className: "text-left", orderable: false },
                        { data: "documents", className: "text-left", orderable: false },
                        { data: "action", className: "text-left", orderable: false },
                    ],
                    dom: 'lf<"scroll-table w-full"t>rip'
                }).columns.adjust();
            }
            dataTableInit();

            $(document).on('click', '.delete-contact', function () {
                if (!confirm("Are you sure?")) return;
                let id = $(this).data('id');

                $.ajax({
                    url: `/contacts/${id}/delete`,
                    type: 'DELETE',
                    data: { _token: "{{ csrf_token() }}" },
                    success: function (res) {
                        alert(res.message);
                        table.ajax.reload();
                    }
                });
            });
            $(document).on('click', '.edit-contact', function () {
                let id = $(this).data('id');
                window.location.href = `/contacts/${id}/edit`;
            });
        });
    </script>
@endsection