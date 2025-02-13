<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
        margin: 0;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #dc3545;
        transition: .4s;
        border-radius: 24px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked+.slider {
        background-color: #198754;
    }

    input:checked+.slider:before {
        transform: translateX(26px);
    }
</style>
@if (isset($data) && count($data) > 0)
    @php
        $record_id = $offset;
    @endphp
    <table class="table k-table table-hover">
        <thead class="table-light">
            <tr>
                <th width="10px">
                    <input type="checkbox" name="row_check_all" class="row_check_all k-input">
                </th>
                <th>#</th>
                <th>Branch Code</th>
                <th>Branch Address</th>
                <th>Owner Name</th>
                <th>Sign</th>
                <th>Photo</th>
                <th>Created By</th>
                <th>Modified By</th>
                <th>Created At</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $v)
                <tr class="row_{{ $v->id }}">
                    <td>
                        <input type="checkbox" name="row_checkbox[]" class="row_checkbox k-input"
                            value="{{ $v->id }}" data-id="{{ $v->id }}">
                    </td>
                    <td>{{ ++$record_id }}</td>
                    <td><a href="{{ $v->show_route }}">{{ $v->br_code }}</a></td>
                    <td>{{ $v->br_address }}</td>
                    <td>{{ $v->br_owner_name }}</td>
                    <td><img src="{{ asset($v->br_sign) }}" width="30" height="30" /></td>
                    <td><img src="{{ asset($v->br_photo) }}" width="30" height="30" /></td>
                    <td>{{ $v->created_by }}</td>
                    <td>{{ $v->modified_by }}</td>
                    <td>{{ Date('d M, Y', strtotime($v->created_at)) }}</td>
                    <td>
                        <label class="switch">
                            <input type="checkbox" class="status-toggle" data-id="{{ $v->id }}"
                                {{ $v->is_status ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </td>
                    <td>
                        @if ($v->deleted_at == null)
                            <a href="{{ route('admin.branch_master.edit', ['encrypted_id' => Crypt::encryptString($v->id)]) }}"
                                class="btn k-btn-sm k-btn-primary btn-sm">Edit</a>
                            <a href="#" data-id="{{ $v->id }}"
                                class="btn k-btn-sm k-btn-danger btn-sm trash_btn delete{{ $v->id }}">Trash</a>
                        @else
                            <a href="#" data-id="{{ $v->id }}"
                                class="btn k-btn-sm k-btn-primary restore_btn restore{{ $v->id }} btn-sm">Restore</a>
                            <a href="#" data-id="{{ $v->id }}"
                                class="btn k-btn-sm k-btn-danger delete_btn delete{{ $v->id }} btn-sm">Delete</a>
                        @endif
                    </td>
                </tr>
                <?php $page_number++; ?>
            @endforeach
        </tbody>
    </table>
    <div class="text-muted p-2"></div>
@else
    <div class="alert alert-warning" align="center">
        Oops, seems like no records are available.
    </div>
@endif
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).on('change', '.status-toggle', function() {
        let checkbox = $(this);
        let id = checkbox.data('id');

        checkbox.prop('disabled', true);

        $.ajax({
            url: '{{ route('branch.toggle-status') }}',
            type: 'POST',
            data: {
                id: id
            },
            success: function(response) {
                if (response.success) {
                    checkbox.prop('checked', response.new_status);
                    alert('Status updated successfully');
                } else {
                    checkbox.prop('checked', !checkbox.is(':checked'));
                    alert('Failed to update status');
                }
            },
            error: function() {
                checkbox.prop('checked', !checkbox.is(':checked'));
                alert('Error occurred while updating status');
            },
            complete: function() {
                checkbox.prop('disabled', false);
            }
        });
    });
</script>
@if ($pagination['total_records'] > $pagination['item_per_page'])
    <div class="card-header">
        <div class="pl-3">
            <div class="paging_simple_numbers">
                <ul class="pagination">
                    <?php
                    echo paginate_function($pagination['item_per_page'], $pagination['current_page'], $pagination['total_records'], $pagination['total_pages']);
                    ?>
                </ul>
            </div>
        </div>
    </div>
@endif
