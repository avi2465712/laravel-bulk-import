<!DOCTYPE html>
<html>
<head>
    <title>Bulk Import + CRUD</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
</head>
<body>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Import 1 Million Contacts</h3>

    <div>
        <span class="mr-3">
            Welcome, <strong>{{ auth()->user()->name }}</strong>
        </span>

        <form method="POST" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button class="btn btn-sm btn-danger">Logout</button>
        </form>
    </div>
</div>


    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Import Form -->
    <form method="POST" action="/import" enctype="multipart/form-data" class="mb-4">
        @csrf
        <div class="form-row">
            <div class="col">
                <input type="file" name="file" class="form-control" required>
            </div>
            <div class="col">
                <button class="btn btn-success">Upload & Import</button>
            </div>
        </div>
    </form>

    <button class="btn btn-primary mb-3" id="addContact">Add Contact</button>

    <!-- DataTable -->
    <table class="table table-bordered" id="contactsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>First</th>
                <th>Last</th>
                <th>Email</th>
                <th>Mobile</th>
                <th>Action</th>
            </tr>
        </thead>
    </table>

</div>

<!-- Modal -->
<div class="modal fade" id="contactModal">
    <div class="modal-dialog">
        <form id="contactForm">
            @csrf
            <input type="hidden" id="contact_id">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Contact</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <input name="fname" class="form-control mb-2" placeholder="First Name" required>
                    <input name="lname" class="form-control mb-2" placeholder="Last Name">
                    <input name="email" class="form-control mb-2" placeholder="Email">
                    <input name="mobile" class="form-control mb-2" placeholder="Mobile">
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<script>
let table = $('#contactsTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: '/contacts-data',
    columns: [
        { data: 'id' },
        { data: 'fname' },
        { data: 'lname' },
        { data: 'email' },
        { data: 'mobile' },
        { data: 'action', orderable:false, searchable:false }
    ]
});

$('#addContact').click(function () {
    $('#contactForm')[0].reset();
    $('#contact_id').val('');
    $('#contactModal').modal('show');
});

//$('#contactForm').submit(function (e) {
  //  e.preventDefault();

    //let id = $('#contact_id').val();
    //let url = id ? `/contacts/update/${id}` : `/contacts/store`;

    //$.post(url, $(this).serialize(), function () {
      //  $('#contactModal').modal('hide');
       // table.ajax.reload();
    //});
//});

$('#contactForm').submit(function (e) {
    e.preventDefault();

    let id = $('#contact_id').val();
    let url = id ? `/contacts/update/${id}` : `/contacts/store`;

    $.ajax({
        url: url,
        type: 'POST',
        data: $(this).serialize(),
        success: function (res) {
            $('#contactModal').modal('hide');
            table.ajax.reload();
            alert(res.message);
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                alert(errors.mobile[0]); // mobile duplicate message
            }
        }
    });
});


$(document).on('click','.editBtn', function () {
    let id = $(this).data('id');

    $.get(`/contacts/edit/${id}`, function (data) {
        $('#contact_id').val(data.id);
        $('[name=fname]').val(data.fname);
        $('[name=lname]').val(data.lname);
        $('[name=email]').val(data.email);
        $('[name=mobile]').val(data.mobile);
        $('#contactModal').modal('show');
    });
});

$(document).on('click','.deleteBtn', function () {
    if (!confirm('Delete this contact?')) return;

    let id = $(this).data('id');

    $.ajax({
        url: `/contacts/delete/${id}`,
        type: 'DELETE',
        data: {_token: '{{ csrf_token() }}'},
        success: function () {
            table.ajax.reload();
        }
    });
});
</script>

</body>
</html>
