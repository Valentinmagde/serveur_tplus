<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Tontine Plus Pdf</title>

    <style>
        body{
            font-family: raleway, sans-serif;
            margin:0;
            padding:0;
            color: #636b6f;
        }
        ul > li{
            list-style: none;
        }
        .header{
            width:100%;
            background: #ececec;
            text-align: center;
            padding: 5px;
        }

        .information{
            /* width:100%; */
            height:20vh;
            position: relative;
            /* background-color: #636b6f */
        }
        hr {
            overflow: visible;
            text-align: inherit;
            margin: 0 0 20px 0;
            border: 0;
            border-top: 1px solid #e5e5e5
        }
        .sommaire{
            /* width:100%; */
            height: auto;
            /* padding: 5px; */
            position: relative;
            text-align:center;
            margin-top: 2%;
            margin-left: 15%;
            margin-right: 15%;
            /* box-shadow: 1px 5px 20px #ccc */
        }
        .statistiques{
            /* width:100%; */
            height: 10vh;
            /* padding: 5px; */
            position: relative;
            margin-top: 2%;
            margin-left: 5%;
            margin-right: 5%;
            margin-bottom: 2%;
            /* background: #1F4587 */
        }
        .total{
            width: 33.33%;
            position: relative;
            /* background: #ccc; */
            height: 100%;
            text-align: center;
            float: left;
            margin-bottom: 5%
        }
        .sections{
            /* width: 100%; */
            position: relative;
            padding: 2%;
            margin-top:5%,
            margin-bottom:5%,

        }
        .presences{
            /* width:100%; */
            height: auto;
            padding: 5px;
            position: relative;
            text-align:center;
            margin-top: 5%;
            margin-left: 2%;
            margin-right:2%;
            margin-bottom:5%;
            box-shadow: 1px 5px 20px #ccc;

        }


        .finances{
            /* width:100%; */
            height: auto;
            padding: 5px;
            position: relative;
            text-align:center;
            margin-top: 2%;
            margin-left: 2%;
            margin-right:2%;
            margin-bottom:5%;
            box-shadow: 1px 5px 20px #ccc;

        }
        
        .uk-width-1-3{
            width: 30%;
            float: left;
        }
        .uk-width-2-3{
            width: 60%;
            float: left;
        }
        .column {
            float: left;
            width: 33%;
            margin-bottom: 1%;
            text-align: left
            }

            /* Clear floats after the columns */
            .row:after {
            content: "";
            display: table;
            clear: both;
            }

            table {
            border-collapse: collapse;
            width: 100%;
            }

            th, td {
            text-align: left;
            padding: 8px;
            }

            tr:nth-child(even) {background-color: #f2f2f2;}
        
    </style>

</head>
<body>
    <div class="header">
        <h2 style="color:#1F4587;margin:5px; font-weight:bold; font-size:28px">@lang('content.Rapport_de_séance')</h2>
        <h5 style="color:#959595; margin:2px; ">@lang('content.assemblée_générale_du') {{gmdate('Y-m-d H:m',$data['ag']['date_ag'])}}</h5>
    </div>

    <div class="information" >
        <div style="width: 45%; float: left; padding-top:20px;padding-bottom:20px; padding-left: 5%">
            <div style="width: 20%; float:left;">
                <img src="{{$data['association']['logo']}}" width="100px" height="100px" alt="" style="border-radius: 50%; box-shadow: 1px 1px 15px black">
            </div>
            <div style="margin-left: 10%;width: 50%; float:left;">
                <h3 style="color:rgba(0, 0, 0, 0.808);"><b>{{$data['association']['nom']}}</b></h3>
                <span>{{$data['association']['description']}}</span>
            </div>
        </div>
        <div style="font-size:14px;width: 50%; float: left; background-color: #1F4587; color:white;padding-bottom:20px; margin-bottom:5%">
            <ul >
                <li style="margin-bottom: 15px">
                    <div >
                        <div class="uk-width-1-3 uk-text-left">
                            <b>@lang('content.Hôte'):</b>
                        </div>
                        <div class="uk-width-2-3 uk-text-left ;" style="margin-bottom: 10px">
                            
                            {{$data['hote']}}
                        </div>
                    </div>
                </li>
                <li style="margin-bottom: 15px">
                    <div >
                        <div class="uk-width-1-3 uk-text-left">
                            <b>@lang('content.Date') :</b>
                        </div>
                        <div class="uk-width-2-3 uk-text-left" style="margin-bottom: 10px">
                            {{gmdate('Y-m-d H:m',$data['date_effective'])}}
                        </div>
                    </div>
                </li>
                <li style="margin-bottom: 15px">
                    <div >
                        <div class="uk-width-1-3 uk-text-left">
                            <b>@lang('content.Lieu') :</b>
                        </div>
                        <div class="uk-width-2-3 uk-text-left" style="margin-bottom: 10px">
                            {{$data['lieu']}}
                        </div>
                    </div>
                </li>
                <li style="margin-bottom: 15px">
                    <div >
                        <div class="uk-width-1-3 uk-text-left">
                            <b>@lang('content.Présidence') :</b>
                        </div>
                        <div class="uk-width-2-3 uk-text-left" style="margin-bottom: 10px">
                            {{$data['presidence_name']}}
                        </div>
                    </div>
                </li>
                <li style="margin-bottom: 15px">
                    <div >
                        <div class="uk-width-1-3 uk-text-left">
                            <b>@lang('content.Sécrétaire') :</b>
                        </div>
                        <div class="uk-width-2-3 uk-text-left" style="margin-bottom: 10px">
                            {{$data['secretaire_name']}}
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>

    <div class="sommaire">
        <h3 class="" style="color:#1F4587; text-align: center;margin-top:10%">@lang('content.Ordre_du_jour')</h3>
        <hr class="" style="">
        <ol style="color:#1F4587; ">
            <?php $i = 0; ?>
            @foreach ($data['sections'] as $key=> $section)
            <li style="margin-bottom: 15px;margin-left: 38%;text-align: left;">
                {{$section['titre']}}
            </li>
            @endforeach
            <li style="margin-bottom: 15px;margin-left: 38%;text-align: left;">
                    @lang('content.Présences_des_membres')
            </li>
            <li style="margin-bottom: 15px;margin-left: 38%;text-align: left;">
                @lang('content.Finances')
            </li>
        </ol>
    </div>
    
    <div class="statistiques" >
        <hr>
        <div class="total">
            <h3 class="uk-card-title uk-text-bold "> {{$data['association']['devise']}} {{$data['encaissement_attendu']}}</h3>
            <p>@lang('content.encaissé_attendu') {{$data['encaissement_total']}} @lang('content.attendu')</p>
        </div>
        <div class="total">
            <h3 class="uk-card-title uk-text-bold" >{{$data['association']['devise']}} {{$data['decaissement_attendu']}}</h3>
            <p>@lang('content.décaissé') {{$data['decaissement_total']}} @lang('content.attendu')</p>
        </div>
        <div class="total">
            <h3 class="uk-card-title uk-text-bold" >{{$data['association']['devise']}} {{$data['caisse']}}</h3>
            <p>@lang('content.total_en_caisse')</p>
        </div>
    </div>
    @if(count($data['sections']) > 0)
        <div class="sections">
            <h3  style="color:#1F4587; text-align:center">{{$section['titre']}}</h3>
            <hr class="uk-divider-icon">
            <p style="text-align:justify; padding-left: 2%; padding-right:2%">
                {!!$section['contenu']!!} 
            </p>
        </div>
    @endif

    <div class="presences">
        <h3  style="color:#1F4587; text-align:center">@lang('content.Présences_des_membres')</h3>
        <hr class="uk-divider-icon">
        
       
        <div class="row">
            @foreach ($data['presences'] as $presence)
                @if($presence['status'] == "present")
                    <div class="column">
                        <div class="row">
                            <div class="uk-width-1-3" style="text-align:right">
                                <i class="fa fa-circle uk-text-success" aria-hidden="true"></i> (P) &nbsp;
                            </div>
                            <div class="uk-width-2-3">
                                {{$presence['membre']}}
                            </div>
                        </div>
                    </div>
                @endif
                @if($presence['status'] == "absent")
                    <div class="column">
                        <div class="row">
                            <div class="uk-width-1-3" style="text-align:right">
                                <i class="fa fa-circle uk-text-danger" aria-hidden="true"></i> (A) &nbsp;
                            </div>
                            <div class="uk-width-2-3">
                                {{$presence['membre']}}
                            </div>
                        </div>
                    </div>
                @endif
                @if($presence['status'] == "Excuse")
                    <div class="column">
                        <div class="row">
                            <div class="uk-width-1-3" style="text-align:right">
                                <i class="fa fa-circle uk-text-primary" aria-hidden="true"></i> (E) &nbsp;
                            </div>
                            <div class="uk-width-2-3">
                                {{$presence['membre']}}
                            </div>
                        </div>
                    </div>
                @endif
                @if($presence['status'] == "retard")
                    <div class="column">
                        <div class="row">
                            <div class="uk-width-1-3" style="text-align:right">
                                <i class="fa fa-circle uk-text-warning" aria-hidden="true"></i> (R) &nbsp;
                            </div>
                            <div class="uk-width-2-3">
                                {{$presence['membre']}}
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div> 
    </div>

    <h3  style="color:#1F4587; text-align:center">@lang('content.Finances')</h3>
    <div class="finances">
        <h4 style="color:#1F4587; text-align:center">@lang('content.Situation_financière')</h4>
        <hr class="uk-divider-icon">
        <table>
            <tr>
                <th>@lang('content.Membre')</th>
                <th>@lang('content.Montant_Attendu')</th>
                <th>@lang('content.Montant_Réalisé')</th>
                <th>@lang('content.status')</th>
            </tr>
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
        </table>
       
    </div>

    <div class="finances">
        <h4 style="color:#1F4587; text-align:center">@lang('content.Décaissement')</h4>
        <hr class="uk-divider-icon">
        <table>
            <tr>
                <th>@lang('content.Membre')</th>
                <th>@lang('content.Montant_Attendu')</th>
                <th>@lang('content.Montant_Réalisé')</th>
                <th>@lang('content.status')</th>
            </tr>
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
        </table>
       
    </div>
</body>
</html>