

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- UIkit CSS -->
    <link href="{{asset('font/font-awesome.css')}}" rel="stylesheet">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    {{-- <link href="{{ asset('css/app.css') }}" rel="stylesheet"> --}}
    <style>
        body {
            font-family: Raleway,sans-serif;
            font-size: 14px;
            color: #636b6f;
            background-color: #fff;
            /* background-color: #f5f8fa; */
            margin:0;
            padding:0;
        }

        li{
           list-style: none;
           display: flow-root;
           margin-bottom: 10px;
        }

        .uk-width-1-3{
            width: 30%;
            float: left;
        }
        .uk-width-2-3{
            width: 60%;
            float: left;
        }

        hr {
            overflow: visible;
            text-align: inherit;
            margin: 0 0 20px 0;
            border: 0;
            border-top: 1px solid #e5e5e5
        }
        
    </style>
</head>
<body>
    <div id="app">
    <header style="background-color:#ececec; border-bottom: 1px solid rgb(242, 242, 242);padding:5px; text-align:center" data-uk-sticky="show-on-up: true; animation: uk-animation-slide-top" class="uk-sticky">
        <div class="uk-container" style="background-color: ">
            <nav id="navbar" data-uk-navbar="mode: click;" class="uk-navbar">
                <div class="uk-navbar-center">
                    {{-- <a class="uk-navbar-item uk-logo" href="#" style="text-decoration: none"> --}}
                        <div class="uk-grid uk-grid-medium uk-flex uk-flex-middle" data-uk-grid="">
                            <div class="uk-width-expand uk-text-center">
                                <h2 class="uk-margin-remove uk-text-bold" style="color:#1F4587;margin:5px; font-weight:bold; font-size:28px">@lang('content.Rapport_de_séance')</h2>
                                <h5 class=" uk-text-muted" style="color:#959595; margin:2px">@lang('content.assemblée_générale_du') {{gmdate('Y-m-d H:m',$data['ag']['date_ag'])}}</h5>
                            </div>
                            
                        </div>
                    {{-- </a> --}}
                </div>
            </nav>
        </div>
    </header>
    
    {{-- 
            begin element d'entête de l'association et du rapport
        --}}
        <div>
            <div style="width: 45%; float: left; padding-top:20px;padding-bottom:20px; padding-left: 5%">
                <div style="width: 20%; float:left;">
                    <img src="{{$data['association']['logo']}}" width="100px" height="100px" alt="" style="border-radius: 50%; box-shadow: 1px 1px 15px black">
                </div>
                <div style="margin-left: 10%;width: 50%; float:left;">
                    <h2 class="uk-margin-remove uk-text-bold" style="color:rgb(0, 0, 0);"><b>{{$data['association']['nom']}}</b></h2>
                    <span class="uk-text-medium uk-text-muted">{{$data['association']['description']}}</span>
                </div>
            </div>
            <div style="width: 50%; float: left; background-color: #1F4587; color:white;padding-top:20px;padding-bottom:20px">
                <ul class="uk-list uk-padding uk-margin-small">
                    <li style="margin-bottom: 15px">
                        <div class="uk-child-width-expand" uk-grid>
                            <div class="uk-width-1-3 uk-text-left">
                                <b>@lang('content.Hôte'):</b>
                            </div>
                            <div class="uk-width-2-3 uk-text-left">
                                {{$data['hote']}}
                            </div>
                        </div>
                    </li>
                    <li style="margin-bottom: 15px">
                        <div class="uk-child-width-expand" uk-grid>
                            <div class="uk-width-1-3 uk-text-left">
                                <b>@lang('content.Date') :</b>
                            </div>
                            <div class="uk-width-2-3 uk-text-left">
                                {{gmdate('Y-m-d H:m',$data['date_effective'])}}
                            </div>
                        </div>
                    </li>
                    <li style="margin-bottom: 15px">
                        <div class="uk-child-width-expand " uk-grid>
                            <div class="uk-width-1-3 uk-text-left">
                                <b>@lang('content.Lieu') :</b>
                            </div>
                            <div class="uk-width-2-3 uk-text-left">
                                {{$data['lieu']}}
                            </div>
                        </div>
                    </li>
                    <li style="margin-bottom: 15px">
                        <div class="uk-child-width-expand" uk-grid>
                            <div class="uk-width-1-3 uk-text-left">
                                <b>@lang('content.Présidence') :</b>
                            </div>
                            <div class="uk-width-2-3 uk-text-left">
                                {{$data['presidence_name']}}
                            </div>
                        </div>
                    </li>
                    <li style="margin-bottom: 15px">
                        <div class="uk-child-width-expand" uk-grid>
                            <div class="uk-width-1-3 uk-text-left">
                                <b>@lang('content.Sécrétaire') :</b>
                            </div>
                            <div class="uk-width-2-3 uk-text-left">
                                {{$data['secretaire_name']}}
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    {{-- <section style="display:block">
        <div style="width: 50%; float:left; padding:20px; background:red">
                <div style="width: 20%; float:left;">
                    <img src="{{$data['association']['logo']}}" width="100px" height="100px" alt="" style="border-radius: 50%; box-shadow: 1px 1px 15px black">
                </div>
                <div style="margin-left: 10%;width: 50%; float:left;">
                    <h2 class="uk-margin-remove uk-text-bold" style="color:rgb(0, 0, 0);"><b>{{$data['association']['nom']}}</b></h2>
                    <span class="uk-text-medium uk-text-muted">{{$data['association']['description']}}</span>
                </div>
        </div>
        <div style="background-color: #1F4587; color:white; float:left; width:40%; padding:20px; ">
            <ul class="uk-list uk-padding uk-margin-small">
                <li style="margin-bottom: 15px">
                    <div class="uk-child-width-expand" uk-grid>
                        <div class="uk-width-1-3 uk-text-left">
                            <b>@lang('content.Hôte'):</b>
                        </div>
                        <div class="uk-width-2-3 uk-text-left">
                            {{$data['hote']}}
                        </div>
                    </div>
                </li>
                <li style="margin-bottom: 15px">
                    <div class="uk-child-width-expand" uk-grid>
                        <div class="uk-width-1-3 uk-text-left">
                            <b>@lang('content.Date') :</b>
                        </div>
                        <div class="uk-width-2-3 uk-text-left">
                            {{gmdate('Y-m-d H:m',$data['date_effective'])}}
                        </div>
                    </div>
                </li>
                <li style="margin-bottom: 15px">
                    <div class="uk-child-width-expand " uk-grid>
                        <div class="uk-width-1-3 uk-text-left">
                            <b>@lang('content.Lieu') :</b>
                        </div>
                        <div class="uk-width-2-3 uk-text-left">
                            {{$data['lieu']}}
                        </div>
                    </div>
                </li>
                <li style="margin-bottom: 15px">
                    <div class="uk-child-width-expand" uk-grid>
                        <div class="uk-width-1-3 uk-text-left">
                            <b>@lang('content.Présidence') :</b>
                        </div>
                        <div class="uk-width-2-3 uk-text-left">
                            {{$data['presidence_name']}}
                        </div>
                    </div>
                </li>
                <li style="margin-bottom: 15px">
                    <div class="uk-child-width-expand" uk-grid>
                        <div class="uk-width-1-3 uk-text-left">
                            <b>@lang('content.Sécrétaire') :</b>
                        </div>
                        <div class="uk-width-2-3 uk-text-left">
                            {{$data['secretaire_name']}}
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </section> --}}
    {{-- end element entête--}}

    <div style="magin-top: 30px; margin-left:20px; margin-right: 20px">
        <div class="box-shadow: 1px 2px 10px #ccc; text-align:center">
            &nbsp;
        </div>
    </div>

    <div style="magin-top: 30px; margin-left:12%; margin-right: 12%">
        <div style="box-shadow: 1px 10px 60px #ccc; padding:10px;">
            <h3 class="" style="color:#1F4587; text-align: center;">@lang('content.Ordre_du_jour')</h3>
            <hr class="" style="">
            <ul class="" style="color:#1F4587;">
                <?php $i = 0; ?>
                @foreach ($data['sections'] as $key=> $section)
                <li style="margin-bottom: 15px">
                    <div class="uk-child-width-expand" uk-grid>
                        <div class="uk-width-1-3 uk-text-right" style="text-align:right">
                            <b>{{$key+1}} : </b>
                        </div>
                        <div class="uk-width-2-3 uk-text-left" style="padding-left: 15px">
                            {{$section['titre']}}
                        </div>
                    </div>
                </li>
                @endforeach
                <li style="margin-bottom: 15px">
                    <div class="uk-child-width-expand" uk-grid>
                        <div class="uk-width-1-3 uk-text-right" style="text-align:right">
                            <b>{{$i+1}}:</b>
                        </div>
                        <div class="uk-width-2-3 uk-text-left" style="padding-left: 15px">
                           @lang('content.Présences_des_membres')
                        </div>
                    </div>
                </li>
                <li style="margin-bottom: 15px">
                    <div class="uk-child-width-expand" uk-grid>
                        <div class="uk-width-1-3 uk-text-right" style="text-align:right">
                            <b>{{$i+2}}:</b>
                        </div>
                        <div class="uk-width-2-3 uk-text-left" style="padding-left: 15px">
                            @lang('content.Finances')
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    
    <div style="margin-right: 5%; margin-left: 5%; margin-top: 5%;">
      
        <div style="width: 30%; text-align:center;float:left">
            <h1 class="uk-card-title uk-text-bold " style="font-size: 30px">{{$data['association']['devise']}} {{$data['encaissement_attendu']}}</h1>
            <p>@lang('content.encaissé_attendu') {{$data['encaissement_total']}} @lang('content.attendu')</p>
        </div>
        <div style="width: 30%; text-align:center;float:left">
            <h1 class="                           uk-card-title uk-text-bold" style="font-size: 30px">{{$data['association']['devise']}} {{$data['decaissement_attendu']}}</h1>
            <p>@lang('content.décaissé') {{$data['decaissement_total']}} @lang('content.attendu')</p>
        </div>
        <div style="width: 30%; text-align:center;float:left">
            <h1 class="uk-card-title uk-text-bold" style="font-size: 30px">{{$data['association']['devise']}} {{$data['caisse']}}</h1>
            <p>@lang('content.total_en_caisse')</p>
        </div>
    </div>

    @if(count($data['sections']) > 0)
        <section >
            <div class=" uk-child-width-auto uk-flex-center" uk-grid>
                @foreach ($data['sections'] as $section)
                    <div class="" >
                        <section class="uk-section  uk-padding-small" style="background-color: rgba(0, 0, 0, 0)">

                            <h3 class="uk-margin-remove uk-text-bold uk-text-center" style="color:#1F4587">{{$section['titre']}}</h3>
                            <hr class="uk-divider-icon">
                            <p class="uk-container uk-text-muted uk-text-justify uk-padding-remove">
                                {!!$section['contenu']!!}
                            </p>
                        </section>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
    
    <section class="uk-section  uk-padding-small" style="background-color: rgba(0, 0, 0, 0)">

        <h3 class="uk-margin-remove uk-text-bold uk-text-center" style="color:#1F4587">@lang('content.Présences_des_membres')</h3>
        <hr class="uk-divider-icon">

        <div class="uk-child-width-1-1 uk-grid-match" uk-grid>
            <div>
                <div class="uk-card uk-card-default uk-card-small uk-card-body uk-text-center">
                    <div class="uk-child-width-1-4" uk-grid>
                        @foreach ($data['presences'] as $presence)
                            @if($presence['status'] == "present")
                                <div style="margin-top: 10px;">
                                    <div class="uk-child-width-1-2" uk-grid>
                                        <div class="uk-width-auto uk-text-left">
                                            <i class="fa fa-circle uk-text-success" aria-hidden="true"></i> (P) &nbsp;
                                        </div>
                                        <div class="uk-width-auto uk-text-left uk-padding-remove">
                                            {{$presence['membre']}}
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if($presence['status'] == "absent")
                                <div style="margin-top: 10px;">
                                    <div class="uk-child-width-1-2" uk-grid>
                                        <div class="uk-width-auto uk-text-left">
                                            <i class="fa fa-circle uk-text-danger" aria-hidden="true"></i> (A) &nbsp;
                                        </div>
                                        <div class="uk-width-auto uk-text-left uk-padding-remove">
                                            {{$presence['membre']}}
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if($presence['status'] == "excuse")
                                <div style="margin-top: 10px;">
                                    <div class="uk-child-width-1-2" uk-grid>
                                        <div class="uk-width-auto uk-text-left">
                                            <i class="fa fa-circle uk-text-primary" aria-hidden="true"></i> (E) &nbsp;
                                        </div>
                                        <div class="uk-width-auto uk-text-left uk-padding-remove">
                                            {{$presence['membre']}}
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if($presence['status'] == "retard")
                                <div style="margin-top: 10px;">
                                    <div class="uk-child-width-1-2" uk-grid>
                                        <div class="uk-width-auto uk-text-left">
                                            <i class="fa fa-circle uk-text-warning" aria-hidden="true"></i> (R) &nbsp;
                                        </div>
                                        <div class="uk-width-auto uk-text-left uk-padding-remove">
                                            {{$presence['membre']}}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="uk-section  uk-padding-small" style="background-color: rgba(0, 0, 0, 0)">

        <h3 class="uk-margin-remove uk-text-bold uk-text-center" style="color:#1F4587">@lang('content.Finances')</h3>
        <div class="uk-child-width-1-1 uk-grid-match" uk-grid>
            <div>
                <div class="uk-card uk-card-default uk-card-small uk-card-body ">
                    <h4 class="uk-margin-remove uk-text-bold uk-text-center" style="color:#1F4587">@lang('content.Situation_financière')</h4>
                    <hr class="uk-divider-icon">
                    <table class="uk-table uk-table-small uk-table-divider">
                        <thead>
                            <tr>
                                <th>@lang('content.Membre')</th>
                                <th>@lang('content.Montant_Attendu')</th>
                                <th>@lang('content.Montant_Réalisé')</th>
                                <th>@lang('content.Status')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data['situation_financiere'] as $item)
                                <tr>
                                    <td>{{$item['membre']}}</td>
                                    <td>{{$item['montant_attendu']}}</td>
                                    <td>{{$item['montant_realise']}}</td>
                                    <td>
                                        @if($item['montant_attendu'] > $item['montant_realise'])
                                            @lang('content.En_cours')
                                        @else
                                            @lang('content.Cloturé')
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
            </div>
            <div>
                <div class="uk-card uk-card-default uk-card-small uk-card-body ">
                    <h4 class="uk-margin-remove uk-text-bold uk-text-center" style="color:#1F4587">@lang('content.Décaissement')</h4>
                <hr class="uk-divider-icon">
        
                <table class="uk-table uk-table-small uk-table-divider">
                    <thead>
                        <tr>
                            <th>@lang('content.Membre')</th>
                            <th>@lang('content.Montant_Attendu')</th>
                            <th>@lang('content.Montant_Réalisé')</th>
                            <th>@lang('content.Status')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data['decaissement'] as $item)
                            <tr>
                                <td>{{$item['membre']}}</td>
                                <td>{{$item['montant_attendu']}}</td>
                                <td>{{$item['montant_realise']}}</td>
                                <td>
                                    @if($item['montant_attendu'] > $item['montant_realise'])
                                       @lang('content.En_cours')
                                    @else
                                        @lang('content.Cloturé')
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
               
            </div>
        </div>
    </section>
</div>
{{-- 
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script> --}}
     <!-- UIkit JS -->
    
</body>
</html>
