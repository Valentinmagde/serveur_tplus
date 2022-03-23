@markdown
---
title: {{$data['association']['nom']}}
author: [{{$data['secretaire_name']}}]
date: @lang('content.assemblée_générale_du') {{gmdate('Y-m-d H:m',$data['ag']['date_ag'])}}
subject: "Markdown"
keywords: [Markdown, Example]
subtitle: {{$data['association']['description']}}
lang: {{$data['association']['langue']}}
titlepage: true,
titlepage-rule-color: "360049"
titlepage-background: "Background3.pdf"
header-left: "\\hspace{1cm}"
header-center: "Tontine.Plus"
header-right: "Page \\thepage"
footer-left: "\\thetitle"
footer-center: "Tontine.Plus"
footer-right: "\\theauthor"
...

### @lang('content.Ordre_du_jour')

> | Titre | valeur |
> | ----------- | ----------- |
> | **@lang('content.Hôte')** | {{$data['hote']}} |
> | **@lang('content.Date')** | {{gmdate('Y-m-d H:m',$data['date_effective'])}} |
> | **@lang('content.Lieu')** | {{$data['lieu']}} |
> | **@lang('content.Présidence')** | {{$data['presidence_name']}} |
> | **@lang('content.Sécrétaire')** | {{$data['secretaire_name']}} | 


### @lang('content.Ordre_du_jour')

@foreach ($data['sections'] as $key=> $section)
1. {{$section['titre']}}
@endforeach
1. @lang('content.Présences_des_membres')
1. @lang('content.Finances')


### **@lang('content.statistique')**

> @lang('content.encaissé_attendu') {{$data['encaissement_total']}} @lang('content.attendu')
```sh
{{$data['association']['devise']}} {{$data['encaissement_attendu']}}
```

> @lang('content.décaissé') {{$data['decaissement_total']}} @lang('content.attendu')
```sh
{{$data['association']['devise']}} {{$data['decaissement_attendu']}}
```

> @lang('content.total_en_caisse')
```sh
{{$data['association']['devise']}} {{$data['caisse']}}
```

### {{$section['titre']}}
{!!$section['contenu']!!}


### @lang('content.Présences_des_membres')

| @lang('content.Membre') | @lang('content.status') |
| ----------- | ----------- |
@foreach ($data['presences'] as $presence)
| {{$presence['membre']}} | {{$presence['status']}} |
@endforeach

### @lang('content.Finances')

#### @lang('content.Situation_financière')

| @lang('content.Membre') | @lang('content.Montant_Attendu') | @lang('content.Montant_Réalisé') | @lang('content.status') |
| ----------- | ----------- | ----------- | ----------- |
@foreach ($data['situation_financiere'] as $item)
| {{$item['membre']}} | {{$item['montant_attendu']}} | {{$item['montant_realise']}} |  @if($item['montant_attendu'] > $item['montant_realise']) @lang('content.En_cours') @else @lang('content.Cloturé') @endif |
@endforeach

#### @lang('content.Décaissement')

| @lang('content.Membre') | @lang('content.Montant_Attendu') | @lang('content.Montant_Réalisé') | @lang('content.status') |
| ----------- | ----------- | ----------- | ----------- |
@foreach ($data['situation_financiere'] as $item)
| {{$item['membre']}} | {{$item['montant_attendu']}} | {{$item['montant_realise']}} |  @if($item['montant_attendu'] > $item['montant_realise']) @lang('content.En_cours') @else @lang('content.Cloturé') @endif |
@endforeach


@endmarkdown