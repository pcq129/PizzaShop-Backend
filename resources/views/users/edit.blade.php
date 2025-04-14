@include('common.header')

<form id="editUser" class="mainBody ms-3" action="{{ route('users.update', $user->id) }}" method="POST">
    @csrf
                @method('PUT')
    {{-- <label for="name">Name</label>
    <input name="name" type="text">

    <label for="email">Email</label>
    <input name="email" type="text">

    <label for="phone">Phone</label>
    <input name="phone" type="text">

    <label for="address">Address</label>
    <input name="address" type="text"> --}}

    <label class="form-label" for="name">User Name</label>
    <input class="form-control w-50" name="userName" type="text" required value="{{ $user->user_name }}">
    <label class="form-label" for="name">First Name</label>
    <input class="form-control w-50" name="firstName" type="text" required value="{{ $user->first_name }}">
    <label class="form-label" for="name">Last Name</label>
    <input class="form-control w-50" name="lastName" type="text" required value="{{ $user->last_name }}">

    <label class="form-label" for="email">Email</label>
    <input class="form-control w-50" name="email" type="email" required value="{{ $user->email }}">

    <label class="form-label" for="phone">Phone</label>
    {{-- <input class="form-control w-50" id="phoneInput" name="phone" type="string" pattern="[0-9]"  min="0" length="10" required value="{{(int)$user->phone}}"> --}}
    <input type="text" class="form-control w-50" name="phone" value="{{str_replace(['+', '-'], '', filter_var($user->phone, FILTER_SANITIZE_NUMBER_INT));}}"  onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))" />

    <label class="form-label" for="address">Address</label>
    <input class="form-control w-50" name="address" type="text" required value="{{ $user->address }}">


    <button class="btn mt-3" type="submit">Save</button>
</form>

@include('common.footer')
