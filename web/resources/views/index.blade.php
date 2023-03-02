@extends('layouts.app')

@section('title', 'TreeView')

@section('content')
    <div class="container">
        <h1>TreeView</h1>
        <form class="d-flex my-2" action="{{ route('search') }}" method="POST">
            @csrf
            <input class="form-control me-2" type="search" placeholder="Search" name="url" aria-label="Search" style="background-color: #f1f1f1; border-radius: 25px;">
            <button class="btn btn-outline-info" type="submit" style="border-radius: 25px;"><i class="bi bi-search"></i>search</button>
        </form>
        @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    </div>
    @if (isset($tree))
        <div class="container">
            <ul class="list-group">
                @foreach ($tree as $node)
                <li>
                    <a href="{{ $node['url'] }}">{{ $node['text'] }}</a>
                    @if (!empty($node['children']))
                    @include('partials.treeview', ['children' => $node['children']])
                    @endif
                </li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection
