@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card text-md-center">
                    Hand 1 wins {{ $hand1wins }} time(s)
                </div>
            </div>
            <div class="col-md-12">
                <table class="table">
                    <thead class="thead-light">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Hand1</th>
                        <th scope="col">Hand1 Cards</th>
                        <th scope="col">Hand2</th>
                        <th scope="col">Hand2 Cards</th>
                        <th scope="col">Result</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($rounds as $key => $round)
                    <tr>
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ $round['hand1'] }}</td>
                        <td>{{ $round['hand1_cards'] }}</td>
                        <td>{{ $round['hand2'] }}</td>
                        <td>{{ $round['hand2_cards'] }}</td>
                        <td>{{ $round['result'] == 0 ? "Tie" : ($round['result'] == 1 ? "Hand 1 wins" : "Hand 2 wins") }}</td>
                    </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
