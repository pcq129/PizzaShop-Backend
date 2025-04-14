@include('common.header')

{{-- model --}}

<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">Confirm Delete</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to delete this user?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          {{-- <button type="button" class="btn btn-primary">Delete</button> --}}
          <label for="submit-form" class="btn btn-danger" tabindex="0">Submit</label>
        </div>
      </div>
    </div>
  </div>
</div>



{{-- main body --}}

<div class="container">
<table class="table table-striped">
    <thead>
        <td>Name</td>
        <td>Phone</td>
        <td>Email</td>
        <td>Address</td>
        <td>Actions</td>
    </thead>
    <tbody>
        @foreach($users as $user)
           <tr>
            <td>{{$user->first_name}}</td>
            <td>{{$user->phone}}</td>
            <td>{{$user->email}}</td>
            <td>{{$user->address}}</td>
            <td>
                <a class="btn btn-info" href="{{ route('users.show',$user->id) }}">Show</a>
                <a class="btn btn-primary" href="{{ route('users.edit',$user->id) }}">Edit</a>
                <form action="{{ route('users.destroy',$user->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                        <button type="submit" class="btn btn-danger"  data-bs-target="#exampleModal">
                                Delete
                        </button>
                        {{-- <button type="submit" class="btn btn-danger">Delete</button> --}}

                        <input type="submit" id="submit-form" value="{{ $user }}" hidden>
                </form>
            </td>
           </tr>
        @endforeach
    </tbody>
    {{-- {{ $users->links() }} --}}
</table>
<div class="d-flex">

<a href="users/create" class="btn rounded bg-primary text-white  mt-2 mb-2">Add User</a>
</div>
</div>



@include('common.footer')
