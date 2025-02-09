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
                <th>company code</th>
                <th>Bike car</th>
                <th>company name</th>
                <th>company logo</th>
                <th>Status</th>
                <th>Created At</th>
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
                    <td>{{ $v->com_code }}</td>
                    <td>{{ $v->bike_car }}</td>
                    <td><a href="{{ $v->show_route }}">{{ $v->com_name }}</a></td>
                    <td><img src="{{ asset($v->com_logo) }}" width="30" height="30" /></td>
                    <td><span class="badge bg-danger">{{ getStatusText($v->is_status) }}</span></td>
                    <td>{{ Date('d M, Y', strtotime($v->created_at)) }}</td>
                    <td>
                        @if ($v->deleted_at == null)
                            <a href="{{ route('admin.company_master.edit', ['encrypted_id' => Crypt::encryptString($v->id)]) }}"
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
        <tbody>
    </table>
    <div class="text-muted p-2"></div>
@else
    <div class="alert alert-warning" align="center">
        Oops, seems like no records are available.
    </div>
@endif

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
