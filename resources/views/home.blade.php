@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <table class="table">
                    <thead class="thead-light">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Poker Rounds</th>
                        <th scope="col" class="text-right">
                            <a href="{{ route('upload') }}" class="btn btn-secondary">Upload new round</a>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($rounds as $key => $round)
                    <tr>
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ $round['name'] }}</td>
                        <td class="text-right">
                            <a href="{{ route('round', $round['id']) }}">View</a>
                        </td>
                    </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
