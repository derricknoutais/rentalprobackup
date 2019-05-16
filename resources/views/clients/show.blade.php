@extends('layouts.app')

@section('content')
    <client-show inline-template>
        <div class="container">
            <h1 class="text-center mt-5">{{ $client->nom . ' ' . $client->prenom }}</h1>
            <div class="row">
                <div class="col">
                    <div class="row mt-5">
                        <div class="col">Nom:</div>
                        <div class="col">Numéro Phone 1:</div>
                        <div class="col">Addresse Maill:</div>
                    </div>
                    <div class="row">
                        <div class="col">{{ $client->nom }}</div>
                        <div class="col">{{ $client->phone1 }}</div>
                        <div class="col">{{ $client->mail }}</div>
                    </div>
                    <div class="row mt-3">
                        <div class="col">Nº Permis</div>
                        <div class="col">Adresse</div>
                        <div class="col">Nombre Locations</div>
                    </div>
                    <div class="row">
                        <div class="col">{{ $client->numero_permis }}</div>
                        <div class="col">{{ $client->adresse }}</div>
                        <div class="col"></div>
                    </div>
                </div>
                <div class="col">
                    <img src="/uploads/{{ $client->permis }}" width="100%" />
                </div>
                
            </div>
            
            {{-- Information client --}}
            <div>
                
            </div>
            {{-- Contrats Clients --}}
            <div>
                <div class="container">
                    <h3 class="text-center mt-5">Liste Contrat</h3>
                    <table class="table table-hover table-bordered mt-5">
                        <thead>
                            <tr>
                                <th>Contrat Nº</th>
                                <th>Immatriculation</th>
                                <th>Date du</th>
                                <th>Date au</th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($client->contrats as $contrat)
                                <tr class="pointer" @click="relocateTo('/contrat/{{ $contrat->id }}')">
                                    <td scope="row">
                                        <a href="/contrat/{{ $contrat->id }}">{{ $contrat->numéro }}</a>
                                    </td>
                                    <td>
                                        <a href="/voiture/{{ $contrat->voiture->id }}">{{ $contrat->voiture->immatriculation }}</a>
                                    </td>
                                    <td>{{ $contrat->check_out->format('d-M-Y')  }}</td>
                                    <td>{{ $contrat->check_in->format('d-M-Y') }}</td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
    </client-show>
@endsection