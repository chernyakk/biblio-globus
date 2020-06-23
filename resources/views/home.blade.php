@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    You are logged in!
                </div>
            </div>
            <div class="align-content-center">
                Make your choose and press the button:
                <form action="/json" method="post" name="city">
                    @csrf
                    <select name="cities">
                        @foreach($towns as $town => $townId)
                            <option value="{{$townId}}">{{$town}}</option>
                        @endforeach
                    </select>
                    <input type="submit" class="button-blue"/>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
