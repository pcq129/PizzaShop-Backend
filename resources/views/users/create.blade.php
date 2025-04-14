@extends('layout')
{{-- @extends('layout') --}}

{{-- @include('common.header') --}}
@section('content')
<form id="editUser" class="mainBody ms-3" action="{{ route('users.store') }}" method="POST">
    @csrf
    @method('POST')
    {{-- <label for="name">Name</label>
    <input name="name" type="text">

    <label for="email">Email</label>
    <input name="email" type="text">

    <label for="phone">Phone</label>
    <input name="phone" type="text">

    <label for="address">Address</label>
    <input name="address" type="text"> --}}

    <label class="form-label" for="name">User Name</label>
    <input class="form-control w-50" name="userName" type="text" required>
    <label class="form-label" for="name">First Name</label>
    <input class="form-control w-50" name="firstName" type="text" required>
    <label class="form-label" for="name">Last Name</label>
    <input class="form-control w-50" name="lastName" type="text" required>

    <label class="form-label" for="email">Email</label>
    <input class="form-control w-50" name="email" type="email" required >

    <label class="form-label" for="phone">Phone</label>
    <input class="form-control w-50" id="phoneInput" name="phone" type="string" pattern="[0-9]"  min="0" length="10" required >
    {{-- <input type="text" class="form-control w-50" name="phone"  /> --}}

    <label class="form-label" for="address">Address</label>
    <input class="form-control w-50" name="address" type="text" required >

    <label class="form-label" for="name">Password</label>
    <input class="form-control w-50" name="password" type="text" required>


    <button class="btn mt-3" type="submit">Save</button>
</form>
@endsection

{{-- @include('common.footer') --}}
