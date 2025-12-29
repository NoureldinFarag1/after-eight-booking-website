<div class="card filter-card mb-3">
    <div class="card-body">
        <form action="{{ $route }}" method="GET" class="row g-3 align-items-center">
            <div class="col-md-6">
                <label for="q" class="form-label">Search</label>
                <input type="text" class="form-control" id="q" name="q" placeholder="Search by name or email..." value="{{ $q ?? '' }}">
            </div>
            <div class="col-md-4">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" @selected(($status ?? '') === 'active')>Active</option>
                    <option value="inactive" @selected(($status ?? '') === 'inactive')>Inactive</option>
                    <option value="deleted" @selected(($status ?? '') === 'deleted')>Deleted</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>
