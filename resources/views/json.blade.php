@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <table>
                    <thead>
                    <tr>
                        <th>Город</th>
                        <th>Город (на английском)</th>
                        <th>Код в БД</th>
                        <th>Аэропорт</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($cities as $city)
                        <tr>
                            <td>{{$city->title_ru}}</td>
                            <td>{{$city->title_en}}</td>
                            <td>{{$city->id}}</td>
                            <td>
                                @if ($city->airports)
                                    @foreach($city->airports as $airport)
                                        {{$airport->code . ' '}}
                                    @endforeach
                                @else Нет аэропортов
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
