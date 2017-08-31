@if (count($errors) > 0)
    <div class="ui visible negative message">
        <div class="header">
            @if (count($errors) === 1)
                There was an error when submitting the form
            @else
                There were some errors when submitting the form
            @endif
        </div>
        <div class="ui list">
            @foreach ($errors->all('<div class="item"><i class="warning icon"></i>:message</div>') as $error)
                {!! $error !!}
            @endforeach
        </div>
    </div>
@endif

