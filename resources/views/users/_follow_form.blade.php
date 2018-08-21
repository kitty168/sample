@if ($user->id !== Auth::user()->id)
    {{--当用户访问自己的个人页面时，不显示关注表单--}}
    {{--即 只有访问非自己页面时才显示--}}
    <div id="follow_form">
        @if (Auth::user()->isFollowing($user->id))
            <form action="{{ route('followers.destroy', $user->id) }}" method="post">
                {{ csrf_field() }}
                {{ method_field('DELETE') }}
                <button type="submit" class="btn btn-sm">取消关注</button>
            </form>
        @else
            <form action="{{ route('followers.store', $user->id) }}" method="post">
                {{ csrf_field() }}
                <button class="btn btn-sm btn-primary">关注</button>
            </form>
        @endif
    </div>
@endif