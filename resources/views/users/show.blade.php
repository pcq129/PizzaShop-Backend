@include('common.header')
<div>
    <table class="table table-striped">
        <tr>
            <td>User Name:</td>
            <td>{{ $user->user_name }}</td>
        </tr>
        <tr>
            <td>First Name:</td>
            <td>{{ $user->first_name }}</td>
        </tr>
        <tr>
            <td>Last Name:</td>
            <td> {{ $user->last_name }}</td>
        </tr>
        <tr>
            <td>Phone Number:</td>
            <td>{{ $user->phone }}</td>
        </tr>
        <tr>
            <td>email: </td>
            <td>{{ $user->email }}</td>
        </tr>
        <tr>
            <td>address:</td>
            <td>{{ $user->address }}</td>
        </tr>

    </table>
</div>
@include('common.footer')
