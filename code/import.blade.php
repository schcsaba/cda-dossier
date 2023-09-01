@extends('layouts.default')

@section('head')
    @parent
    @include('package-fleet-fuel::parts.templates.fuelcard-template')
    @include('layouts.tooltip', ['title' => 'IMPORT FICHIER CARBURANT', 'body' => '<p>Retrouvez ici l’ensemble de vos Importations de fichiers de carburant.</p>'])
    <link rel="stylesheet" href="{{ URL::asset('css/jquery.fileupload.css') }}"/>
@endsection

@section('navigation')
@endsection

@section('content')
    <section class="content refonte-parc fluid">
        <article>
            <div class="row">
                <section>
                    <div id="card-page" v-cloak>

                        <h1>Import fichier carburant</h1>
                        @if(session('success'))
                            <p class="alert alert-success" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                {{ session('success') }}
                            </p>
                        @endif

                        @if(session('error'))
                            <p class="alert alert-danger" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                {!!session('error')!!}
                            </p>
                        @endif

                        @if(session('fuelSubscriptionError'))
                            <p class="alert alert-danger" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                {!!session('fuelSubscriptionError')!!}
                            </p>
                        @endif

                        @permission('park-export-fueltransactions')

                            <div class="card">
                                <div class="card-header">
                                    <h4>Import</h4>
                                </div>
                                <div class="card-block">
                                    <form class="form-inline import" action="">
                                        <div class="form-group">
                                            <span class="label">Type de fichier</span>
                                            <div class="btn-group" data-toggle="buttons">
                                                <label class="btn btn-secondary" style="margin-bottom:0"
                                                       @click="supplier = 1">
                                                    <input type="radio" name="supplier" autocomplete="off" value="1">
                                                    <img src="{{ asset('img/fleet/fuel/total-energie-icone.png') }}"
                                                         class="img-responsive" style="height:13px"/>
                                                    Total &nbsp <i class="fa" aria-hidden="true"></i>
                                                </label>
                                                <label class="btn btn-secondary" style="margin-bottom:0"
                                                       @click="supplier = 3">
                                                    <input type="radio" name="supplier" autocomplete="off" value="3">
                                                    <img src="{{ asset('img/fleet/fuel/shell.png') }}"
                                                         class="img-responsive" style="height:13px"/>
                                                    Shell &nbsp <i class="fa" aria-hidden="true"></i>
                                                </label>
                                                <label class="btn btn-secondary" style="margin-bottom:0"
                                                       @click="supplier = 5">
                                                    <input type="radio" name="supplier" autocomplete="off" value="5">
                                                    <img src="{{ asset('img/fleet/fuel/leclerc-energie-icone.png') }}"
                                                         class="img-responsive" style="height:13px"/>
                                                    Leclerc Energie &nbsp <i class="fa" aria-hidden="true"></i>
                                                </label>
                                                <label class="btn btn-secondary" style="margin-bottom:0"
                                                       @click="supplier = 7">
                                                    <input type="radio" name="supplier" autocomplete="off" value="7">
                                                    <img src="{{ asset('img/fleet/fuel/dkv.png') }}"
                                                         class="img-responsive" style="height:11px"/>
                                                    DKV &nbsp <i class="fa" aria-hidden="true"></i>
                                                </label>
                                                <label class="btn btn-secondary" style="margin-bottom:0"
                                                       @click="supplier = 8">
                                                    <input type="radio" name="supplier" autocomplete="off" value="8">
                                                    <img src="{{ asset('img/fleet/fuel/AS24.png') }}"
                                                         class="img-responsive" style="height:13px"/>
                                                    AS24 &nbsp <i class="fa" aria-hidden="true"></i>
                                                </label>
                                                <label class="btn btn-secondary" style="margin-bottom:0"
                                                       @click="supplier = 9">
                                                    <input type="radio" name="supplier" autocomplete="off" value="9">
                                                    <img src="{{ asset('img/fleet/fuel/c2a-icone.png') }}"
                                                         class="img-responsive" style="height:13px"/>
                                                    C2A &nbsp <i class="fa" aria-hidden="true"></i>
                                                </label>
                                                <label class="btn btn-secondary" style="margin-bottom:0"
                                                       @click="supplier = 10">
                                                    <input type="radio" name="supplier" autocomplete="off" value="10">
                                                    <img src="{{ asset('img/fleet/fuel/lccc-icone.png') }}"
                                                         class="img-responsive" style="height:13px"/>
                                                    LCCC &nbsp <i class="fa" aria-hidden="true"></i>
                                                </label>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div v-show="supplier" class="card">
                                <div class="card-header">
                                    <h4>Téléchargez un fichier <span class="text-capitalize">@{{ findBrand(supplier) }}</span></h4>
                                </div>
                                <div class="card-block">
                                    <form @submit.prevent="importFile">

                                        {{ csrf_field() }}

                                        <div class="form-inline form-group">
                                            <div class="form-group">
                                                <span class="label">1. Téléchargez un fichier <span class="text-capitalize">@{{ findBrand(supplier) }}</span> de type <span v-if="'' != fileUploadAcceptType()" v-html="fileUploadAcceptType()"></span> : </span>
                                                <span class="btn btn-default fileinput-button">
                                                <i class="fa fa-plus"></i>
                                                <span class="btn-file-upload">Ajouter un fichier
                                                    <span v-if="'' != fileUploadAcceptType()" v-html="'('+fileUploadAcceptType()+')'"></span>
                                                </span>
                                                <input id="fileupload" type="file" name="file" :accept="fileUploadAcceptType()" :disabled="fileuploadDisabled == 1" @change="handleFileInput">
                                            </span>
                                            </div>
                                            <div class="form-group" v-if="file != ''">
                                                <p class="form-control-static">@{{ file.name }}</p>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <span class="label">2. Cliquez sur le bouton d'importation : </span>
                                            <button type="submit" class="btn btn-outline-primary"
                                                    :disabled="supplier == null || file == '' "><i
                                                    class="fa fa-cloud-upload" aria-hidden="true"></i> Importer
                                            </button>
                                        </div>
                                    </form>
                                    <div v-if="guide.length">
                                        <span class="label">Pour savoir comment récupérer un fichier <span class="text-capitalize">@{{ findBrand(supplier) }}</span>, cliquez sur le bouton info : </span>
                                        <span class="btn btn-outline-info" id="btnGuide">
                                                <i class="fa fa-info-circle"></i> Guide de téléchargement
                                            </span>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="modalGuide" role="dialog" aria-labelledby="modalGuide" aria-hidden="true">
                                <div class="modal-dialog" role="document" style="max-width: 80%; display: flex; justify-content: center">
                                    <div class="modal-content" style="height: 690px; overflow-y: scroll">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>

                                            <h4 class="modal-title" id="myModalLabel">
                                                Comment télécharger un fichier <span class="text-capitalize">@{{ findBrand(supplier) }}</span> ?
                                            </h4>
                                        </div>
                                        <div class="modal-body" style="display: flex; flex-direction: column; row-gap: 15px">
                                            <div v-for="guideStep in guide" style="display: flex; flex-direction: column; align-items: center; row-gap: 10px">
                                                <h5>@{{ guideStep[0] }}</h5>
                                                <img :src="guideStep[1]" style="width: 90%">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        @endpermission
                        <div class="card">
                            <div class="card-header">
                                <h4>Historique</h4>
                            </div>
                            <div class="card-block">
                                <div class="col-lg-6 gauges body-histo">
                                    <div class="monthes-label">
                                        <span class="text-capitalize">@{{ months[0] }}</span>
                                        <span class="text-capitalize">@{{ months[1] }}</span>
                                        <span class="text-capitalize">@{{ months[2] }}</span>
                                        <span class="text-capitalize">@{{ months[3] }}</span>
                                    </div>
                                    <div class="gauge" v-for="supplier in listSuppliers">
                                        <div class="gauge-header">
                                            <img style="width:95%;height: 95%" :src="'../img/fleet/fuel/'+ supplier.name+ '.png'" :class="!supplier.isUsed ? 'grey-header' : ''"/>
                                        </div>
                                        <div class="gauge-bg">
                                                <div class="gauge-fill-ko" :style="{'height':(supplier.isUsed ? redTimeline : '0') + 'px', 'bottom':'0px'}"></div>
                                                <div class="gauge-fill-ok" v-for="timeline in supplier.timelines"
                                                     :style="{'height':timeline.heightTimeline + 'px', 'bottom':timeline.heightToBottom + 'px'}"
                                                     data-toggle="tooltip" data-placement="right" title=""
                                                     :data-original-title="'Du ' +  timeline.from + ' Au ' + timeline.to"></div>


                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 table-container" >

                                    <div class="text-xs-center m-t-3" v-show="loader">
                                        <i class="fa fa-circle-o-notch fa-spin fa-3x text-muted"></i>
                                    </div>

                                    <div class="table-responsive" v-show="historyForList.length > 0">
                                        <table class="table table-striped table-hover table-sm">
                                            <thead class="thead-inverse">
                                            <tr>
                                                <th>Fournisseur</th>
                                                <th>Période</th>
                                                <th>Date</th>
                                                <th>Utilisateur</th>
                                                <th></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="n in waitingImportStart">
                                                <td colspan="5" style="text-align: center;"><i class="fa fa-circle-o-notch fa-spin fa-3x text-muted"></i></td>
                                            </tr>
                                            <tr v-for="n in importStartError" style="background-color: rgba(250, 80, 58, 0.25); color: rgb(255, 0, 0);">
                                                <td class="display-flex align-items-center"
                                                    style="vertical-align: middle;">
                                                    <img :src=" '../img/fleet/fuel/' + findBrand(supplierForStaticListElements) +'.png' "
                                                         style="width:40px; margin-right:5px;  margin-left:5px"/>
                                                    <span class="text-capitalize">@{{ findBrand(supplierForStaticListElements) }}</span>
                                                </td>
                                                <td colspan="4" style="text-align: left">Le traitement du fichier n'a pas pu démarrer!<br>Une erreur s'est produite
                                                </td>
                                            </tr>
                                            <tr v-for="n in importStartSuccess">
                                                <td class="display-flex align-items-center"
                                                    style="vertical-align: middle">
                                                    <img :src=" '../img/fleet/fuel/' + findBrand(supplierForStaticListElements) +'.png' "
                                                         style="width:40px; margin-right:5px;  margin-left:5px"/>
                                                    <span class="text-capitalize">@{{ findBrand(supplierForStaticListElements) }}</span>
                                                </td>
                                                <td colspan="4" style="text-align: left"><span style="color: rgb(18, 125, 0)">Le traitement du fichier a démarré</span><br><span style="color: rgb(139, 145, 154)">Le fichier est en cours de traitement </span><i class="fa fa-circle-o-notch fa-spin text-muted"></i>
                                                </td>
                                            </tr>
                                            <tr v-for="item in historyForList" track-by="idImport">
                                                <td v-if="item.status == 3 || item.status == 1  || item.status == 2" class="display-flex align-items-center"
                                                    style="vertical-align: middle">
                                                    <img :src=" '../img/fleet/fuel/' + findBrand(item.idSupplier) +'.png' "
                                                         style="width:40px; margin-right:5px;  margin-left:5px"/>
                                                    <span class="text-capitalize">@{{ findBrand(item.idSupplier) }}</span>
                                                </td>
                                                <td v-if="item.status == 4 || item.status == 0"
                                                    style="vertical-align: middle; background-color: rgba(250, 80, 58, 0.25);">
                                                    <img :src=" '../img/fleet/fuel/' + findBrand(item.idSupplier) +'.png' "
                                                         style="width:40px; margin-right:5px;  margin-left:5px;"/>
                                                    <span class="text-capitalize">@{{ findBrand(item.idSupplier) }}</span>
                                                </td>
                                                <td v-if="item.status == 2" colspan="4" style="text-align: left"><span style="color: rgb(18, 125, 0)">Le traitement du fichier a démarré</span><br><span style="color: rgb(139, 145, 154)">Le fichier est en cours de traitement </span><i class="fa fa-circle-o-notch fa-spin text-muted"></i>
                                                </td>
                                                <td v-if="item.status == 4 || item.status == 0" colspan="2" style="text-align: left; background-color: rgba(250, 80, 58, 0.25);"><span style="color: rgb(18, 125, 0)">Le traitement du fichier a démarré</span><br><span style="color: rgb(139, 145, 154)">Le fichier est en cours de traitement</span><br><span style="color: rgb(255, 0, 0);">Une erreur s'est produite</span>
                                                </td>
                                                <td v-if="item.status == 3 || item.status == 1" style="vertical-align: middle">
                                                    Du @{{ convertDate(item.from) }}<br>Au @{{ convertDate(item.to) }}
                                                </td>
                                                <td v-if="item.status == 3 || item.status == 1" style="vertical-align: middle">
                                                    @{{ convertDateTime(item.date) }}
                                                </td>
                                                <td v-if="item.status == 3 || item.status == 1" style="vertical-align: middle">
                                                    @{{ item.user ? item.user : '-' }}
                                                </td>
                                                <td v-if="item.status == 4 || item.status == 0" style="vertical-align: middle; background-color: rgba(250, 80, 58, 0.25);">
                                                    @{{ item.user ? item.user : '-' }}
                                                </td>
                                                <td v-if="(item.status == 4 || item.status == 0) && !item.uploadError" style="vertical-align: middle; background-color: rgba(250, 80, 58, 0.25);">
                                                    -
                                                </td>
                                                <td v-if="(item.status == 4 || item.status == 0) && item.uploadError" style="vertical-align: middle; color: rgb(255, 0, 0); background-color: rgba(250, 80, 58, 0.25);" data-toggle="tooltip" data-placement="left" data-html="true" data-original-title="@{{ item.uploadError }}">
                                                    <i class="fa fa-solid fa-exclamation-circle" style="cursor: pointer"></i>
                                                </td>
                                                <td v-if="item.status == 3 || item.status == 1" style="vertical-align: middle">
                                                    <a :href="'{{ route('fleet.fuel.transactions') }}?period=' + convertDateForLink(item.from)">
                                                        <i class="fa fa-list-ul"></i>
                                                    </a>
                                                </td>
                                            </tr>

                                            </tbody>
                                        </table>
                                    </div>

                                    <div v-show="historyForList.length == 0 && loader == false">
                                        <h2 class="text-muted text-xs-center m-t-3">
                                            Aucun historique
                                        </h2>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>

                </section>
            </div>
        </article>
    </section>
@endsection

@section('script')
    @parent
    @include('utils.require.js', ['js' => 'sdfFuelCard.vue.js'])
@endsection

@section('requireReady')
    @parent
    require(['vue', 'fuelCard.vue', 'jquery', 'moment'], function (Vue, FuelCard, $, moment) {
        Vue.component('sdf-fuelcard', FuelCard);

        new Vue({
            el: '#card-page',
            data() {
                return {
                    loader: false,
                    supplier: null,
                    fileuploadDisabled: 1,
                    file: '',
                    csrfToken: '',
                    dayDate: null,
                    months: [],
                    historyForBars: [],
                    historyForList: [],
                    prevHistoryForListSize: 0,
                    limit: 300,
                    guide: [],
                    waitingImportStart: 0,
                    importStartSuccess: 0,
                    importStartError: 0,
                    shouldImportHistory: true,
                    supplierForStaticListElements: null,
                    listSuppliers: [
                        {
                            name: 'total-energie-icone',
                            isUsed: false,
                            timelines: []
                        },
                        {
                            name: 'shell',
                            isUsed: false,
                            timelines: []
                        },
                        {
                            name: 'leclerc-energie-icone',
                            isUsed: false,
                            timelines: []
                        },
                        {
                            name: 'dkv',
                            isUsed: false,
                            timelines: []
                        },
                        {
                            name: 'AS24',
                            isUsed: false,
                            timelines: []
                        },
                        {
                            name: 'c2a-icone',
                            isUsed: false,
                            timelines: []
                        },
                        {
                            name: 'lccc-icone',
                            isUsed: false,
                            timelines: []
                        }
                    ],
                    redTimeline: {
                        heightTimeline: 0,
                        heightToBottom: 0
                    }
                };
            },

            watch: {
                supplier(newSupplier, oldSupplier) {
                    if (null !== newSupplier) {
                        this.fileuploadDisabled = 0;
                        this.getGuide(newSupplier);
                    }
                },
            },

            methods: {
                fileUploadAcceptType() {
                    let format = '';

                    if([1,3].includes(this.supplier)) {
                        format = '.txt';
                    } else if([5,7,9,10].includes(this.supplier)) {
                        format = '.csv';
                    } else if([8].includes(this.supplier)) {
                        format = '.dat';
                    }

                    return format;
                },

                buildTimeline() {
                    let differenceDay;
                    let differenceDayToStart;
                    let from;
                    let to;
                    let i = j = k = l = m = n = o = 0;
                    let numberOfDay;
                    let dayHeight;

                    numberOfDay = moment().endOf('month').diff(moment().subtract(2, 'month').startOf('month'), 'days');
                    dayHeight = 450 / numberOfDay;

                    this.historyForBars.forEach((line) => {
                        from = moment(line.from, 'YYYY-MM-DD');
                        to = moment(line.to, 'YYYY-MM-DD');

                        differenceDay = to.diff(from, 'days');
                        differenceDayToStart = from.diff(moment().subtract(2, 'month').startOf('month'), 'days');

                        let heightTimeline = (differenceDay * dayHeight) + dayHeight;
                        let heightToBottom = differenceDayToStart * dayHeight;

                        if (1 == line.idSupplier) {
                            this.listSuppliers[0].isUsed = true;
                            this.listSuppliers[0].timelines.push({ heightTimeline: heightTimeline, heightToBottom: heightToBottom, from: moment(line.from, 'YYYY-MM-DD').format('DD/MM/YYYY'), to: moment(line.to, 'YYYY-MM-DD').format('DD/MM/YYYY') });
                            i++;
                        } else if (3 == line.idSupplier) {
                            this.listSuppliers[1].isUsed = true;
                            this.listSuppliers[1].timelines.push({ heightTimeline: heightTimeline, heightToBottom: heightToBottom, from: moment(line.from, 'YYYY-MM-DD').format('DD/MM/YYYY'), to: moment(line.to, 'YYYY-MM-DD').format('DD/MM/YYYY') });
                            j++;
                        } else if (5 == line.idSupplier) {
                            this.listSuppliers[2].isUsed = true;
                            this.listSuppliers[2].timelines.push({ heightTimeline: heightTimeline, heightToBottom: heightToBottom, from: moment(line.from, 'YYYY-MM-DD').format('DD/MM/YYYY'), to: moment(line.to, 'YYYY-MM-DD').format('DD/MM/YYYY') });
                            k++;
                        } else if (7 == line.idSupplier) {
                            this.listSuppliers[3].isUsed = true;
                            this.listSuppliers[3].timelines.push({ heightTimeline: heightTimeline, heightToBottom: heightToBottom, from: moment(line.from, 'YYYY-MM-DD').format('DD/MM/YYYY'), to: moment(line.to, 'YYYY-MM-DD').format('DD/MM/YYYY') });
                            l++;
                        } else if (8 == line.idSupplier) {
                            this.listSuppliers[4].isUsed = true;
                            this.listSuppliers[4].timelines.push({ heightTimeline: heightTimeline, heightToBottom: heightToBottom, from: moment(line.from, 'YYYY-MM-DD').format('DD/MM/YYYY'), to: moment(line.to, 'YYYY-MM-DD').format('DD/MM/YYYY') });
                            m++;
                        } else if (9 == line.idSupplier) {
                            this.listSuppliers[5].isUsed = true;
                            this.listSuppliers[5].timelines.push({ heightTimeline: heightTimeline, heightToBottom: heightToBottom, from: moment(line.from, 'YYYY-MM-DD').format('DD/MM/YYYY'), to: moment(line.to, 'YYYY-MM-DD').format('DD/MM/YYYY') });
                            n++;
                        } else if (10 == line.idSupplier) {
                            this.listSuppliers[6].isUsed = true;
                            this.listSuppliers[6].timelines.push({ heightTimeline: heightTimeline, heightToBottom: heightToBottom, from: moment(line.from, 'YYYY-MM-DD').format('DD/MM/YYYY'), to: moment(line.to, 'YYYY-MM-DD').format('DD/MM/YYYY') });
                            o++;
                        }
                    }, this);

                    setTimeout(() => {
                        $('[data-toggle="tooltip"]').tooltip();
                    }, 0);

                },

                findBrand(typeId) {
                    if (1 == typeId) {
                        return 'total';
                    } else if (5 == typeId) {
                        return 'Leclerc Energie';
                    } else if (3 == typeId) {
                        return 'shell';
                    } else if (7 == typeId) {
                        return 'dkv';
                    } else if (8 == typeId) {
                        return 'AS24';
                    } else if ( 9 == typeId) {
                        return 'c2a'
                    } else if ( 10 == typeId ) {
                        return 'lccc'
                    }
                },

                getGuide(typeId) {
                    this.guide = [];
                    if (1 == typeId) {
                        this.guide.push(['1/ Aller dans la section « Fournisseurs & Factures »', "{{ asset('img/import/total/01.png') }}"]);
                        this.guide.push(['2/ Cliquez sur le bouton de téléchargement', "{{ asset('img/import/total/02.png') }}"]);
                    } else if (5 == typeId) {
                        this.guide.push(['Récupération des transactions', "{{ asset('img/import/leclerc/01.png') }}"]);
                    } else if (8 == typeId) {
                        this.guide.push(['1/ Aller dans la section « Factures & transactions »', "{{ asset('img/import/as24/01.png') }}"]);
                        this.guide.push(['2/ Cliquez sur INFOSERVICE', "{{ asset('img/import/as24/02.png') }}"]);
                        this.guide.push(['3/ Remplissez le formulaire et cliquez sur le bouton de téléchargement', "{{ asset('img/import/as24/03.png') }}"]);
                    }
                },

                convertDate(date) {
                    return moment(date, 'YYYY-MM-DD').format('DD/MM/YYYY');
                },

                convertDateTime(datetime) {
                    return moment(datetime, 'YYYY-MM-DD HH:mm').format('DD/MM/YYYY HH:mm');
                },

                convertDateForLink(date) {
                    return moment(date, 'YYYY-MM-DD').format('YYYY/MM');
                },

                handleFileInput: function(event) {
                    this.file = event.target.files[0];
                },

                importFile: function(event) {
                    const formData = new FormData();
                    formData.append('file', this.file);
                    this.waitingImportStart++;
                    this.supplierForStaticListElements = this.supplier;

                    let that = this;

                    $.ajax({
                        url: "{{ route('fleet.fuel.import.transactions', ['supplier' => '']) }}/" + that.supplier,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': that.csrfToken
                        },
                        success: function(response) {
                            that.waitingImportStart--;
                            that.importStartSuccess++;
                            that.shouldImportHistory = true;
                            that.file = '';
                            event.target.reset();
                            that.limit++;
                        },
                        error: function(error) {
                            that.waitingImportStart--;
                            that.importStartError++;
                            that.file = '';
                            event.target.reset();
                        }
                    });
                },

                importHistory() {
                    let that = this;
                    if (that.shouldImportHistory) {
                        $.ajax({
                            url: "{{ route('api-gateway',['route' => 'park/fuel/transactions/history/']) }}",
                            global : false,
                            method : 'GET',
                            success(response) {
                                that.historyForBars = response.data;
                                that.buildTimeline();
                            },
                            error(resultat, statut, erreur) {
                                that.loader = false;
                            }
                        });
                        $.ajax({
                            url: "{{ route(
                                'api-gateway',
                                ['route' => 'park/fuel/transactions/history/']) }}" + "?created_at=default&status[]=0&status[]=1&status[]=2&status[]=3&status[]=4&limit=" + that.limit,
                            global : false,
                            method : 'GET',
                            success(response) {
                                that.historyForList = response.data;

                                that.loader = false;
                                if (that.prevHistoryForListSize < that.historyForList.length) {
                                    const diff = that.historyForList.length - that.prevHistoryForListSize;
                                    that.importStartSuccess = that.importStartSuccess-diff >= 0 ? that.importStartSuccess-diff : 0;
                                }
                                that.prevHistoryForListSize = that.historyForList.length;
                                that.checkRecentPendingImports();
                            },
                            error(resultat, statut, erreur) {
                                that.loader = false;
                            }
                        });
                    }
                },

                checkRecentPendingImports() {
                    const currentDate = new Date();
                    const oneHourAgo = currentDate.getTime() - (60 * 60 * 1000);
                    const recentPendingImports = this.historyForList.filter(function (item) {
                                                const historyCreatedAt = new Date(item.date);
                                                return item.status == 2 && historyCreatedAt.getTime() > oneHourAgo;
                                            });
                    this.shouldImportHistory = recentPendingImports.length > 0 ? true : false;
                }
            },

            ready() {

                this.loader = true;

                this.importHistory();

                let intervalId;

                intervalId = setInterval(this.importHistory, 10000);

                this.dayDate = moment();

                this.months = [
                    moment().add(1, 'month').format("MMMM"),
                    moment().format("MMMM"),
                    moment().subtract(1, 'month').format("MMMM"),
                    moment().subtract(2, 'month').format("MMMM"),
                ];

                this.redTimeline = this.dayDate.diff(moment().subtract(2, 'month').startOf('month'), 'days') * 5;

                $('body').on('click', '#btnGuide', function () {
                    $('#modalGuide').modal('show');
                });

                this.csrfToken = "{{ csrf_token() }}";

                setTimeout(() => {
                    $('[data-toggle="tooltip"]').tooltip()
                }, 10000)
            },
        });
    });
@endsection
