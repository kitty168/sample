@extends('layouts.default')
@section('title', $user->name)
@section('content')
    <div class="row">
        <div class="col-md-offset-2 col-md-8">
            <div class="col-md-12">
                <div class="col-md-offset-2 col-md-8">
                    {{--显示用户的个人信息--}}
                    <section class="user_info">
                        @include('shared._user_info', ['user' => $user])
                    </section>
                    {{--显示关注和粉丝以及微博数统计信息--}}
                    <section class="stats">
                        @include('shared._stats', ['user' => $user])
                    </section>
                </div>
            </div>
            <div class="col-md-12">
                @if (Auth::check())
                    {{--登录用户才显示关注和取消关注功能--}}
                    @include('users._follow_form')
                @endif

                @if (count($statuses) > 0)
                    {{--显示用户的微博信息--}}
                    <ol class="statuses">
                        @foreach ($statuses as $status)
                            @include('statuses._status')
                        @endforeach
                    </ol>
                    {!! $statuses->render() !!}
                @endif
            </div>
        </div>
    </div>
@stop