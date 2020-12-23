@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Edit Profile') }}</div>

                <div class="card-body">
                    {{ __('Edit your data!') }}

                    <form method="POST" action="{{ route('users.update', $user->id) }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                        <input type="hidden" name="_method" value="PUT" />
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input
                            type="text"
                            name="name"
                            value="{{ $user->name }}"
                            class="form-control"
                            />
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input
                            type="email"
                            name="email"
                            value="{{ $user->email }}"
                            class="form-control"
                            />
                        </div>
                        <hr/>
                        <div class="form-group">
                            <label for="name">Binance</label>
                            <input
                            type="text"
                            name="b_key"
                            value="{{ $user->b_key }}"
                            placeholder="API Key"
                            class="form-control"
                            />
                            <input
                            type="password"
                            name="b_private"
                            value=""
                            placeholder="Private Key (leave empty to keep old one)"
                            class="form-control"
                            />
                            

                        </div>
                        
                        <p>Favourite coins</p>
                        <p class='small text-mute mb-5'>{{ $fav_coins }}</p>

                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-btn fa-sign-in"></i>Update
                        </button>
                        </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

