@extends('layouts.app')

@section('title', 'Finance Officers')

@section('content')
<div class="container">
    <h1>Finance Officers</h1>
    <a href="{{ route('admin.finance_officers.create') }}" class="btn btn-primary mb-3">New Finance Officer</a>
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead><tr><th>Name</th><th>Email</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($officers as $officer)
                        <tr>
                            <td>{{ $officer->name }}</td>
                            <td>{{ $officer->email }}</td>
                            <td>
                                <a href="{{ route('admin.finance_officers.edit', $officer) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <form action="{{ route('admin.finance_officers.destroy', $officer) }}" method="POST" style="display:inline-block">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $officers->links() }}
        </div>
    </div>
</div>
@endsection
