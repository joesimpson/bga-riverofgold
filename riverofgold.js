/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * RiverOfGold implementation : © joesimpson <1324811+joesimpson@users.noreply.github.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * riverofgold.js
 *
 * RiverOfGold user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

//Tisaac way to debug ;)
var isDebug = window.location.host == 'studio.boardgamearena.com' || window.location.hash.indexOf('debug') > -1;
var debug = isDebug ? console.info.bind(window.console) : function () {};

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    g_gamethemeurl + 'modules/js/Core/game.js',
    g_gamethemeurl + 'modules/js/Core/modal.js',
],
function (dojo, declare) {

    const NB_SHORE_SPACES = 30;

    const TILE_TYPE_SCORING = 1;
    const TILE_TYPE_BUILDING = 2;
    const TILE_TYPE_MASTERY_CARD = 3;

    const TILE_LOCATION_SCORING = 's';
    const TILE_LOCATION_MASTERY_CARD = 'm';
    const TILE_LOCATION_BUILDING_DECK = 'bd';
    const TILE_LOCATION_BUILDING_DECK_ERA_1 = TILE_LOCATION_BUILDING_DECK+'1';
    const TILE_LOCATION_BUILDING_DECK_ERA_2 = TILE_LOCATION_BUILDING_DECK+'2';
    const TILE_LOCATION_BUILDING_ROW ='br';
    const TILE_LOCATION_BUILDING_SHORE = 'sh';

    const CARD_LOCATION_DELIVERED = 'dd';
    const CARD_LOCATION_HAND = 'h';

    const RESOURCE_TYPE_SILK = 1;
    const RESOURCE_TYPE_POTTERY = 2;
    const RESOURCE_TYPE_RICE = 3;
    const RESOURCES = [
        0,
        'silk',//RESOURCE_TYPE_SILK
        'pottery',//RESOURCE_TYPE_POTTERY
        'rice',//RESOURCE_TYPE_RICE
    ];

    const BUILDING_TYPE_PORT =     1;
    const BUILDING_TYPE_MARKET =   2;
    const BUILDING_TYPE_MANOR =    3;
    const BUILDING_TYPE_SHRINE =   4;
    const BUILDING_TYPES = [
        0,
        _('Port'),//BUILDING_TYPE_PORT
        _('Market'),//BUILDING_TYPE_MARKET
        _('Manor'),//BUILDING_TYPE_MANOR
        _('Shrine'),//BUILDING_TYPE_SHRINE
    ];
    
    const MEEPLE_TYPE_SHIP = 1;
    const MEEPLE_TYPE_SHIP_ROYAL = 3;
    const MEEPLE_TYPE_CLAN_MARKER = 2;

    return declare("bgagame.riverofgold", [customgame.game], {
        constructor: function(){
            debug('riverofgold constructor');

            // Fix mobile viewport (remove CSS zoom)
            this.default_viewport = 'width=800';

            this._counters = {};
            
            this._notifications = [
                ['clearTurn', 200],
                ['refreshUI', 200],
                ['refreshHand', 50],
                ['giveMoney', 1300],
                ['spendMoney', 1300],
                ['giveCardTo', 1000],
                ['giveResource', 800],
                ['build', 1300],
                ['newClanMarker', 700],
            ];
        },
        
        ///////////////////////////////////////////////////
        //     _____ ______ _______ _    _ _____  
        //    / ____|  ____|__   __| |  | |  __ \ 
        //   | (___ | |__     | |  | |  | | |__) |
        //    \___ \|  __|    | |  | |  | |  ___/ 
        //    ____) | |____   | |  | |__| | |     
        //   |_____/|______|  |_|   \____/|_|    
        /////////////////////////////////////////////////// 
        /*
            setup:
            
            This method must set up the game user interface according to current game situation specified
            in parameters.
            
            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)
            
            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */
        
        setup: function( gamedatas )
        {
            debug('SETUP', gamedatas);
            
            this.setupTiles();
            this.setupPlayers();
            this.setupInfoPanel();
            this.setupCards();
            this.setupMeeples();
            
            debug( "Ending specific game setup" );

            this.inherited(arguments);
        },
        
        getSettingsConfig() {
            return {
                masteryWidth: {
                  default: 50,
                  name: _('Mastery cards size'),
                  type: 'slider',
                  sliderConfig: {
                    step: 2,
                    padding: 0,
                    range: {
                      min: [30],
                      max: [100],
                    },
                  },
                }, 
                eraTileWidth: {
                  default: 30,
                  name: _('Building board size'),
                  type: 'slider',
                  sliderConfig: {
                    step: 2,
                    padding: 0,
                    range: {
                        //relative to big image dimensions
                      min: [5],
                      max: [50],
                    },
                  },
                }, 
                handWidth: {
                  default: 100,
                  name: _('Hand width'),
                  type: 'slider',
                  sliderConfig: {
                    step: 2,
                    padding: 0,
                    range: {
                      min: [30],
                      max: [200],
                    },
                  },
                }, 
                boardWidth: {
                  default: 100,
                  name: _('River board width'),
                  type: 'slider',
                  sliderConfig: {
                    step: 2,
                    padding: 0,
                    range: {
                      min: [30],
                      max: [100],
                    },
                  },
                }, 
                boardWidth: {
                  default: 100,
                  name: _('River board width'),
                  type: 'slider',
                  sliderConfig: {
                    step: 2,
                    padding: 0,
                    range: {
                      min: [30],
                      max: [100],
                    },
                  },
                }, 
            };
        },
        
        onChangeMasteryWidthSetting(val) {
            document.documentElement.style.setProperty('--rog_mastery_scale', val/100);
        },
        onChangeBoardWidthSetting(val) {
            this.updateLayout();
        },
        onChangeHandWidthSetting(val) {
            document.documentElement.style.setProperty('--rog_hand_scale', val/100);
            this.updateLayout();
        },
        onChangeEraTileWidthSetting(val) {
            document.documentElement.style.setProperty('--rog_era_tile_holder_scale', val/100);
        },
        onChangeDeliveredWidthSetting(val) {
            document.documentElement.style.setProperty('--rog_delivered_scale', val/100);
            this.updateLayout();
        },
       
        ///////////////////////////////////////////////////
        //     _____ _______    _______ ______  _____ 
        //    / ____|__   __|/\|__   __|  ____|/ ____|
        //   | (___    | |  /  \  | |  | |__  | (___  
        //    \___ \   | | / /\ \ | |  |  __|  \___ \ 
        //    ____) |  | |/ ____ \| |  | |____ ____) |
        //   |_____/   |_/_/    \_\_|  |______|_____/ 
        ///////////////////////////////////////////////////
          
        onLeavingState(stateName) {
            this.inherited(arguments);
            this.empty('rog_select_piece_container');
        },

        onEnteringStatePlayerTurn(args){
            debug('onEnteringStatePlayerTurn', args);

            this.addPrimaryActionButton(`btnBuild`, _('Build') , () =>  { this.takeAction('actBuild'); });
            this.addPrimaryActionButton(`btnSail`, _('Sail') , () =>  { this.takeAction('actSail'); });
            this.addPrimaryActionButton(`btnDeliver`, _('Deliver') , () =>  { this.takeAction('actDeliver'); });
        },
        onEnteringStateBuild(args){
            debug('onEnteringStateBuild', args);

            this.selectedTileId = null; 
            this.selectedSpace = null; 
            let shoreSpacesDiv = $(`rog_shore_spaces`);
            let buildingRowDiv = $(`rog_building_row`);
            this.addPrimaryActionButton('btnConfirm', this.fsr(_('Select and pay ${n} Koku'), { n: 0 }), () => {
                //let selectedBuilding = buildingRowDiv.querySelector(`.rog_tile.selected`);
                //let selectedTileId = document.querySelector('.rog_button_building_tile.selected').dataset.id;
                //let selectedSpace = shoreSpacesDiv.querySelector(`.rog_shore_space.selected`).dataset.pos;
                this.takeAction('actBuildSelect', { p: this.selectedSpace,  t: this.selectedTileId});
            }); 
            //DISABLED by default
            $(`btnConfirm`).classList.add('disabled');

            Object.values(args.spaces).forEach((space) => {
                let elt2 = $(`rog_shore_space-${space.id}`);
                this.onClick(`${elt2.id}`, (evt) => {
                    //CLICK SELECT DESTINATION
                    shoreSpacesDiv.querySelectorAll('.rog_shore_space').forEach((elt) => {
                        elt.classList.remove('selected');
                    });
                    let div = evt.target;
                    div.classList.add('selected');
                    this.selectedSpace = div.dataset.pos;
                    $('btnConfirm').innerHTML = this.fsr(_('Select and pay ${n} Koku'), { n: space.cost });
                    if(this.selectedTileId != null){
                        $(`btnConfirm`).classList.remove('disabled');
                    }
                });
            });
            [...buildingRowDiv.querySelectorAll('.rog_tile')].forEach((tile) => {
                //TODO JSA reverse order
                let tileId = tile.dataset.id;
                let buttonId = `btnTile_${tileId}`;
                let callbackTileSelection = (evt) => {
                    document.querySelectorAll('.rog_button_building_tile').forEach( (e) => e.classList.remove('rog_selected_button') );
                    buildingRowDiv.querySelectorAll('.rog_building_slot,.rog_tile').forEach( (e) => e.classList.remove('selected') );
                    tile.classList.toggle('selected');
                    tile.parentNode.classList.toggle('selected');
                    $(buttonId).classList.toggle('rog_selected_button');
                    $(`btnConfirm`).classList.add('disabled');
                    this.selectedTileId = null;
                    if($(buttonId).classList.contains('rog_selected_button')){
                        this.selectedTileId = tileId;
                        if(this.selectedSpace != null){
                            $(`btnConfirm`).classList.remove('disabled');
                        }
                    }
                };
                this.onClick(`${tile.parentNode.id}`, callbackTileSelection);
                this.addImageActionButton(buttonId, `<div class='rog_button_building_tile' data-type='${tile.dataset.type}' data-id='${tileId}'></div>`, callbackTileSelection);
                $(buttonId).classList.add('rog_button_building_tile');
            });
        },
        
        onEnteringStateConfirmTurn(args) {
            this.addPrimaryActionButton('btnConfirmTurn', _('Confirm'), () => {
                this.takeAction('actConfirmTurn');
            });
        },
        
        //////////////////////////////////////////////////////////////
        //    _   _       _   _  __ _           _   _                 
        //   | \ | |     | | (_)/ _(_)         | | (_)                
        //   |  \| | ___ | |_ _| |_ _  ___ __ _| |_ _  ___  _ __  ___ 
        //   | . ` |/ _ \| __| |  _| |/ __/ _` | __| |/ _ \| '_ \/ __|
        //   | |\  | (_) | |_| | | | | (_| (_| | |_| | (_) | | | \__ \
        //   |_| \_|\___/ \__|_|_| |_|\___\__,_|\__|_|\___/|_| |_|___/
        //                                                            
        //    
        //////////////////////////////////////////////////////////////
        notif_giveCardTo(n) {
            debug('notif_giveCardTo: receiving a new card', n);
            if (!$(`rog_card-${n.args.card.id}`)) this.addCard(n.args.card, this.getVisibleTitleContainer());
            this.slide(`rog_card-${n.args.card.id}`, this.getCardContainer(n.args.card));
        },
        notif_giveResource(n) {
            debug('notif_giveResource: receiving new resources', n);
            this.gainPayResource(n.args.player_id, RESOURCES[n.args.res_type], n.args.n);
        },
        notif_spendMoney(n) {
            debug('notif_spendMoney: spending money', n);
            this.gainPayMoney(n.args.player_id, -n.args.n);
        },
    
        notif_giveMoney(n) {
            debug('notif_giveMoney: gaining money', n);
            this.gainPayMoney(n.args.player_id, n.args.n);
        },
    
        notif_build(n) {
            debug('notif_build: building a tile to the shore', n);
            if (!$(`rog_tile-${n.args.tile.id}`)) this.addTile(n.args.tile, this.getVisibleTitleContainer());
            let fromDiv = $(`rog_building_slot-${n.args.from}`);
            this.slide(`rog_tile-${n.args.tile.id}`, this.getTileContainer(n.args.tile), {  
                from: fromDiv.id, 
                phantom: false,
            });
        },
        notif_newClanMarker(n) {
            debug('notif_newClanMarker', n);
            if (!$(`rog_meeple-${n.args.meeple.id}`)) this.addMeeple(n.args.meeple, this.getVisibleTitleContainer());
            this.slide(`rog_meeple-${n.args.meeple.id}`, this.getMeepleContainer(n.args.meeple), { });
        },
        ///////////////////////////////////////////////////
        notif_clearTurn(n) {
            debug('notif_clearTurn: restarting turn/step', n);
            this.cancelLogs(n.args.notifIds);
        },
        notif_refreshUI(n) {
            debug('notif_refreshUI: refreshing UI', n);
            ['players', 'cards', 'tiles'].forEach((value) => {
                this.gamedatas[value] = n.args.datas[value];
            });
    
            this.setupCards();
            this.setupTiles();
            this.setupMeeples();
    
            this.forEachPlayer((player) => {
                let pId = player.id;
                this.scoreCtrl[pId].toValue(player.score);
                this._counters[pId].money.toValue(player.money);
                this._counters[pId].silk.toValue(player.silk);
                this._counters[pId].pottery.toValue(player.pottery);
                this._counters[pId].rice.toValue(player.rice);
                this._counters[pId].dieFace.toValue(player.die);
                
            });
        },
        notif_refreshHand(n) {
            debug('notif_refreshHand: refreshing hand', n);
            this.gamedatas.cards = this.gamedatas.cards.concat(n.args.hand);
    
            this.setupCards(); 
        },
        
        ///////////////////////////////////////////////////
        //    _    _ _   _ _     
        //   | |  | | | (_) |    
        //   | |  | | |_ _| |___ 
        //   | |  | | __| | / __|
        //   | |__| | |_| | \__ \
        //    \____/ \__|_|_|___/
        //                       
        ///////////////////////////////////////////////////
        onScreenWidthChange() {
            if (this.settings) this.updateLayout();
        },
    
        updateLayout() {
            if (!this.settings) return;
            const ROOT = document.documentElement;
    
            const WIDTH = $('rog_main_zone').getBoundingClientRect()['width'];
            const BOARD_WIDTH = 2726;
    
            let widthScale = ((this.settings.boardWidth / 100) * WIDTH) / BOARD_WIDTH,
            scale = widthScale;
            ROOT.style.setProperty('--rog_board_display_scale', scale);
                    
            //const PLAYER_HAND_WIDTH = 300;
            //let remainingWidth = WIDTH - $('rog_resizable_river_board').getBoundingClientRect()['width'];
            //widthScale = ((this.settings.handWidth / 100) * remainingWidth) / PLAYER_HAND_WIDTH;
            //ROOT.style.setProperty('--rog_hand_scale', widthScale);
        },
        
        undoToStep(stepId) {
            this.checkAction('actRestart');
            this.takeAction('actUndoToStep', { stepId }, false);
        },

        ////////////////////////////////////////////////////////////
        // _____                          _   _   _
        // |  ___|__  _ __ _ __ ___   __ _| |_| |_(_)_ __   __ _
        // | |_ / _ \| '__| '_ ` _ \ / _` | __| __| | '_ \ / _` |
        // |  _| (_) | |  | | | | | | (_| | |_| |_| | | | | (_| |
        // |_|  \___/|_|  |_| |_| |_|\__,_|\__|\__|_|_| |_|\__, |
        //                                                 |___/
        ////////////////////////////////////////////////////////////
        formatIcon(name, n = null) {
            let type = name;
            let text = n == null ? '' : `<span>${n}</span>`;
            return `<div class="rog_icon_container rog_icon_container_${type}">
                <div class="rog_icon rog_icon_${type}">${text}</div>
                </div>`;
        },
        formatString(str) {
            debug('formatString', str);
            const ICONS = [];
        
            ICONS.forEach((name) => {
                // WITH TEXT
                const regex = new RegExp('<' + name + ':([^>]+)>', 'g');
                str = str.replaceAll(regex, this.formatIcon(name, '$1'));
                // WITHOUT TEXT
                str = str.replaceAll(new RegExp('<' + name + '>', 'g'), this.formatIcon(name));
            });
            str = str.replace(/__([^_]+)__/g, '<span class="action-card-name-reference">$1</span>');
            str = str.replace(/\*\*([^\*]+)\*\*/g, '<b>$1</b>');
        
            return str;
        },
        ////////////////////////////////////////
        //  ____  _
        // |  _ \| | __ _ _   _  ___ _ __ ___
        // | |_) | |/ _` | | | |/ _ \ '__/ __|
        // |  __/| | (_| | |_| |  __/ |  \__ \
        // |_|   |_|\__,_|\__, |\___|_|  |___/
        //                |___/
        ////////////////////////////////////////

        setupPlayers() {
            let currentPlayerNo = 1;
            let nPlayers = 0;
            this.forEachPlayer((player) => {
                let isCurrent = player.id == this.player_id;
                let divPanel = `player_panel_content_${player.color}`;
                this.place('tplPlayerPanel', player, divPanel, 'after');
                if(isCurrent) this.place('tplPlayerHand', player, 'rog_upper_zone','last');
                this.place('tplPlayerDeliveredCards', player, 'rog_players_deliveries');
                
                let pId = player.id;
                this._counters[pId] = {
                    money: this.createCounter(`rog_counter_${pId}_money`, player.money),
                    silk: this.createCounter(`rog_counter_${pId}_silk`, player.silk),
                    pottery: this.createCounter(`rog_counter_${pId}_pottery`, player.pottery),
                    rice: this.createCounter(`rog_counter_${pId}_rice`, player.rice),
                    dieFace: this.createCounter(`rog_counter_${pId}_dieFace`, player.die),
                };
                this.addCustomTooltip(`rog_reserve_${pId}_money`, _('Koku'));
                this.addCustomTooltip(`rog_reserve_${pId}_rice`, _('Rice'));
                this.addCustomTooltip(`rog_reserve_${pId}_silk`, _('Silk'));
                this.addCustomTooltip(`rog_reserve_${pId}_pottery`, _('Pottery'));
                this.addCustomTooltip(`rog_reserve_${pId}_dieFace`, _('Die'));

                nPlayers++;
                if (isCurrent) currentPlayerNo = player.no;
            });
    
            // Order them
            this.forEachPlayer((player) => {
                let isCurrent = player.id == this.player_id;
                //let 1 space for personal board
                let order = ((player.no - currentPlayerNo + nPlayers) % nPlayers) + 1;
                if (isCurrent) order = 1;
                $(`rog_player_delivered_resizable-${player.id}`).style.order = order;
                $(`rog_player_delivered_resizable-${player.id}`).style['border-color'] ='#'+ player.color;
            });
    
            this.updateFirstPlayer();
        },
        updateFirstPlayer() {
            let pId = this.gamedatas.firstPlayer;
            debug("updateFirstPlayer()",pId);
            if(pId == null) return;

        },
        ////////////////////////////////////////////////////////
        //  ___        __         ____                  _
        // |_ _|_ __  / _| ___   |  _ \ __ _ _ __   ___| |
        //  | || '_ \| |_ / _ \  | |_) / _` | '_ \ / _ \ |
        //  | || | | |  _| (_) | |  __/ (_| | | | |  __/ |
        // |___|_| |_|_|  \___/  |_|   \__,_|_| |_|\___|_|
        ////////////////////////////////////////////////////////
        
        updatePlayerOrdering() {
            debug("updatePlayerOrdering");
            this.inherited(arguments);
            dojo.place('player_board_config', 'player_boards', 'first');
        },
        setupInfoPanel() {
            debug("setupInfoPanel");
                    
            dojo.place(this.tplConfigPlayerBoard(), 'player_boards', 'first');
            this._counters['turn'] = this.createCounter('rog_counter_turn',this.gamedatas.turn);
            
            let chk = $('help-mode-chk');
            dojo.connect(chk, 'onchange', () => this.toggleHelpMode(chk.checked));
            this.addTooltip('help-mode-switch', '', _('Toggle help/safe mode.'));
            
            this._settingsModal = new customgame.modal('showSettings', {
                class: 'rog_popin',
                closeIcon: 'fa-times',
                title: _('Settings'),
                closeAction: 'hide',
                verticalAlign: 'flex-start',
                contentsTpl: `<div id='rog_settings'>
                    <div id='rog_settings_header'></div>
                    <div id="settings-controls-container"></div>
                </div>`,
            });
        },
        
        tplConfigPlayerBoard() {
            let turn = this.gamedatas.turn;
            return `
            <div class='player-board' id="player_board_config">
                <div id="player_config" class="player_board_content">
                <div class="player_config_row" id="turn_counter_wrapper">
                  ${_('Turn')} <span id='rog_counter_turn'>${turn}</span>
                </div>
                <div class="player_config_row">
                    <div id="help-mode-switch">
                        <input type="checkbox" class="checkbox" id="help-mode-chk" />
                        <label class="label" for="help-mode-chk">
                            <div class="ball"></div>
                        </label>
                        <svg aria-hidden="true" focusable="false" data-prefix="fad" data-icon="question-circle" class="svg-inline--fa fa-question-circle fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><g class="fa-group"><path class="fa-secondary" fill="currentColor" d="M256 8C119 8 8 119.08 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 422a46 46 0 1 1 46-46 46.05 46.05 0 0 1-46 46zm40-131.33V300a12 12 0 0 1-12 12h-56a12 12 0 0 1-12-12v-4c0-41.06 31.13-57.47 54.65-70.66 20.17-11.31 32.54-19 32.54-34 0-19.82-25.27-33-45.7-33-27.19 0-39.44 13.14-57.3 35.79a12 12 0 0 1-16.67 2.13L148.82 170a12 12 0 0 1-2.71-16.26C173.4 113 208.16 90 262.66 90c56.34 0 116.53 44 116.53 102 0 77-83.19 78.21-83.19 106.67z" opacity="0.4"></path><path class="fa-primary" fill="currentColor" d="M256 338a46 46 0 1 0 46 46 46 46 0 0 0-46-46zm6.66-248c-54.5 0-89.26 23-116.55 63.76a12 12 0 0 0 2.71 16.24l34.7 26.31a12 12 0 0 0 16.67-2.13c17.86-22.65 30.11-35.79 57.3-35.79 20.43 0 45.7 13.14 45.7 33 0 15-12.37 22.66-32.54 34C247.13 238.53 216 254.94 216 296v4a12 12 0 0 0 12 12h56a12 12 0 0 0 12-12v-1.33c0-28.46 83.19-29.67 83.19-106.67 0-58-60.19-102-116.53-102z"></path></g></svg>
                    </div>

                    <div id="show-settings">
                    <svg  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
                        <g>
                        <path class="fa-secondary" fill="currentColor" d="M638.41 387a12.34 12.34 0 0 0-12.2-10.3h-16.5a86.33 86.33 0 0 0-15.9-27.4L602 335a12.42 12.42 0 0 0-2.8-15.7 110.5 110.5 0 0 0-32.1-18.6 12.36 12.36 0 0 0-15.1 5.4l-8.2 14.3a88.86 88.86 0 0 0-31.7 0l-8.2-14.3a12.36 12.36 0 0 0-15.1-5.4 111.83 111.83 0 0 0-32.1 18.6 12.3 12.3 0 0 0-2.8 15.7l8.2 14.3a86.33 86.33 0 0 0-15.9 27.4h-16.5a12.43 12.43 0 0 0-12.2 10.4 112.66 112.66 0 0 0 0 37.1 12.34 12.34 0 0 0 12.2 10.3h16.5a86.33 86.33 0 0 0 15.9 27.4l-8.2 14.3a12.42 12.42 0 0 0 2.8 15.7 110.5 110.5 0 0 0 32.1 18.6 12.36 12.36 0 0 0 15.1-5.4l8.2-14.3a88.86 88.86 0 0 0 31.7 0l8.2 14.3a12.36 12.36 0 0 0 15.1 5.4 111.83 111.83 0 0 0 32.1-18.6 12.3 12.3 0 0 0 2.8-15.7l-8.2-14.3a86.33 86.33 0 0 0 15.9-27.4h16.5a12.43 12.43 0 0 0 12.2-10.4 112.66 112.66 0 0 0 .01-37.1zm-136.8 44.9c-29.6-38.5 14.3-82.4 52.8-52.8 29.59 38.49-14.3 82.39-52.8 52.79zm136.8-343.8a12.34 12.34 0 0 0-12.2-10.3h-16.5a86.33 86.33 0 0 0-15.9-27.4l8.2-14.3a12.42 12.42 0 0 0-2.8-15.7 110.5 110.5 0 0 0-32.1-18.6A12.36 12.36 0 0 0 552 7.19l-8.2 14.3a88.86 88.86 0 0 0-31.7 0l-8.2-14.3a12.36 12.36 0 0 0-15.1-5.4 111.83 111.83 0 0 0-32.1 18.6 12.3 12.3 0 0 0-2.8 15.7l8.2 14.3a86.33 86.33 0 0 0-15.9 27.4h-16.5a12.43 12.43 0 0 0-12.2 10.4 112.66 112.66 0 0 0 0 37.1 12.34 12.34 0 0 0 12.2 10.3h16.5a86.33 86.33 0 0 0 15.9 27.4l-8.2 14.3a12.42 12.42 0 0 0 2.8 15.7 110.5 110.5 0 0 0 32.1 18.6 12.36 12.36 0 0 0 15.1-5.4l8.2-14.3a88.86 88.86 0 0 0 31.7 0l8.2 14.3a12.36 12.36 0 0 0 15.1 5.4 111.83 111.83 0 0 0 32.1-18.6 12.3 12.3 0 0 0 2.8-15.7l-8.2-14.3a86.33 86.33 0 0 0 15.9-27.4h16.5a12.43 12.43 0 0 0 12.2-10.4 112.66 112.66 0 0 0 .01-37.1zm-136.8 45c-29.6-38.5 14.3-82.5 52.8-52.8 29.59 38.49-14.3 82.39-52.8 52.79z" opacity="0.4"></path>
                        <path class="fa-primary" fill="currentColor" d="M420 303.79L386.31 287a173.78 173.78 0 0 0 0-63.5l33.7-16.8c10.1-5.9 14-18.2 10-29.1-8.9-24.2-25.9-46.4-42.1-65.8a23.93 23.93 0 0 0-30.3-5.3l-29.1 16.8a173.66 173.66 0 0 0-54.9-31.7V58a24 24 0 0 0-20-23.6 228.06 228.06 0 0 0-76 .1A23.82 23.82 0 0 0 158 58v33.7a171.78 171.78 0 0 0-54.9 31.7L74 106.59a23.91 23.91 0 0 0-30.3 5.3c-16.2 19.4-33.3 41.6-42.2 65.8a23.84 23.84 0 0 0 10.5 29l33.3 16.9a173.24 173.24 0 0 0 0 63.4L12 303.79a24.13 24.13 0 0 0-10.5 29.1c8.9 24.1 26 46.3 42.2 65.7a23.93 23.93 0 0 0 30.3 5.3l29.1-16.7a173.66 173.66 0 0 0 54.9 31.7v33.6a24 24 0 0 0 20 23.6 224.88 224.88 0 0 0 75.9 0 23.93 23.93 0 0 0 19.7-23.6v-33.6a171.78 171.78 0 0 0 54.9-31.7l29.1 16.8a23.91 23.91 0 0 0 30.3-5.3c16.2-19.4 33.7-41.6 42.6-65.8a24 24 0 0 0-10.5-29.1zm-151.3 4.3c-77 59.2-164.9-28.7-105.7-105.7 77-59.2 164.91 28.7 105.71 105.7z"></path>
                        </g>
                    </svg>
                    </div>
                </div>
            </div>
            `;
        },
        tplPlayerPanel(player) {
            return `<div class='rog_panel'>
            <div class="rog_first_player_holder"></div>
            <div class='rog_player_infos'>
                ${this.tplResourceCounter(player, 'money')}
                ${this.tplResourceCounter(player, 'silk')}
                ${this.tplResourceCounter(player, 'rice')}
                ${this.tplResourceCounter(player, 'pottery')}
                ${this.tplResourceCounter(player, 'dieFace')}
            </div>
            </div>`;
        },
        /**
         * Use this tpl for any counters that represent qty of tokens
         */
        tplResourceCounter(player, res, nbSubIcons = null, totalValue = null) {
            let totalText = totalValue ==null ? '' : `<span id='rog_counter_${player.id}_${res}_total' class='rog_resource_${res}_total'></span> `;
            return `
            <div class='rog_player_resource rog_resource_${res}'>
                <span id='rog_counter_${player.id}_${res}' 
                class='rog_resource_${res}'></span>${totalText}${this.formatIcon(res, nbSubIcons)}
                <div class='rog_reserve' id='rog_reserve_${player.id}_${res}'></div>
            </div>
            `;
        },
            
        gainPayMoney(pId, n, targetSource = null) {
            return this.gainPayResource(pId,'money', n, targetSource);
        },
        
        gainPayResource(pId,resourceType, n, targetSource = null) {
            if (this.isFastMode()) {
                this._counters[pId][resourceType].incValue(n);
                return Promise.resolve();
            }
    
            let elem = `<div id='rog_${resourceType}_animation'>
                ${Math.abs(n)}
                <div class="rog_icon_container rog_icon_container_${resourceType}">
                    <div class="rog_icon rog_icon_${resourceType}"></div>
                </div>
                </div>`;
            $('page-content').insertAdjacentHTML('beforeend', elem);
    
            if (n > 0) {
                return this.slide(`rog_${resourceType}_animation`, `rog_counter_${pId}_${resourceType}`, {
                    from: targetSource || this.getVisibleTitleContainer(),
                    destroy: true,
                    phantom: false,
                    duration: 800,
                }).then(() => this._counters[pId][resourceType].incValue(n));
            } else {
                this._counters[pId][resourceType].incValue(n);
                return this.slide(`rog_${resourceType}_animation`, targetSource || this.getVisibleTitleContainer(), {
                    from: `rog_counter_${pId}_${resourceType}`,
                    destroy: true,
                    phantom: false,
                    duration: 800,
                });
            }
        },
            
        ////////////////////////////////////////////////////////
        //    ____              _
        //   / ___|__ _ _ __ __| |___
        //  | |   / _` | '__/ _` / __|
        //  | |__| (_| | | | (_| \__ \
        //   \____\__,_|_|  \__,_|___/
        //////////////////////////////////////////////////////////

        setupCards() {
            // This function is refreshUI compatible
            //destroy previous cards
            document.querySelectorAll('.rog_card[id^="rog_card-"]').forEach((oCard) => {
                this.destroy(oCard);
            });
            let cardIds = this.gamedatas.cards.map((card) => {
                if (!$(`rog_card-${card.id}`)) {
                    this.addCard(card);
                }
        
                let o = $(`rog_card-${card.id}`);
                if (!o) return null;
        
                let container = this.getCardContainer(card);
                if (o.parentNode != $(container)) {
                    dojo.place(o, container);
                }
                o.dataset.state = card.state;
        
                return card.id;
            });
        },
    
        addCard(card, location = null) {
            debug('addCard',card);
            if ($('rog_card-' + card.id)) return;
    
            let o = this.place('tplCard', card, location == null ? this.getCardContainer(card) : location);
            let tooltipDesc = this.getCardTooltip(card);
            if (tooltipDesc != null) {
                this.addCustomTooltip(o.id, tooltipDesc.map((t) => this.formatString(t)).join('<br/>'));
            }
    
            return o;
        },
    
        getCardTooltip(card) {
            let cardDatas = card;
            let desc = [this.fsr(_('${card_type} ${region}'), { card_type: cardDatas.title, region: cardDatas.region })];
            let div = this.tplCard(cardDatas,'_tmp');
            return [`<div class='rog_card_tooltip'><h1>${desc}</h1>${div}</div>`];
        },
    
        tplCard(card, prefix ='') {
            return `<div class="rog_card" id="rog_card${prefix}-${card.id}" data-id="${card.id}" data-type="${card.type}">
                </div>`;
        },
    
        getCardContainer(card) {
            if (card.location == CARD_LOCATION_HAND) {
                return $(`rog_cards_hand-${card.pId}`);
            }
            if (card.location == CARD_LOCATION_DELIVERED) {
                return $(`rog_cards_delivered-${card.pId}`);
            }
    
            console.error('Trying to get container of a card', card);
            return 'game_play_area';
        },
            
        tplPlayerHand(player) {
            return `<div class='rog_player_hand_resizable'>
                <div id='rog_player_hand-${player.id}' class='rog_player_hand' data-color='${player.color}'>
                    <div class='player-name' style='color:#${player.color}'>${_('My hand')}</div>
                    <div class='rog_cards_hand' id='rog_cards_hand-${player.id}'></div>
                </div>
            </div>`;
        },
        tplPlayerDeliveredCards(player) {
            return `<div class='rog_player_delivered_resizable' id='rog_player_delivered_resizable-${player.id}'>
                <div id='rog_player_delivered-${player.id}' class='rog_player_delivered' data-color='${player.color}'>
                    <h3 ${this.fsr(_('${player_name} delivered'), { player_name:this.coloredPlayerName(player.name)}) }</h3>
                    <div class='rog_cards_delivered' id='rog_cards_delivered-${player.id}'></div>
                </div>
            </div>`;
        },

        ////////////////////////////////////////////////////////
        //  _____ _ _
        // |_   _(_) | ___  ___
        //   | | | | |/ _ \/ __|
        //   | | | | |  __/\__ \
        //   |_| |_|_|\___||___/
        //////////////////////////////////////////////////////////

        setupTiles() {
            for(k=1;k<=NB_SHORE_SPACES;k++){
                if($(`rog_shore_space-${k}`)) continue;
                this.place(`tplShoreSpace`,k, $(`rog_shore_spaces`));
            }
            /*
            dojo.empty('rog_mastery_cards');
            dojo.empty('rog_building_era-1');
            dojo.empty('rog_building_era-2');
            document.querySelectorAll('#rog_scoring_tiles [id^="rog_scoring_tile-"]').forEach((oCard) => {
                this.destroy(oCard);
            });
            document.querySelectorAll('.rog_building_slot').forEach((oCard) => {
                dojo.empty(oCard);
            });
            */
            //Destroy previous tiles
            document.querySelectorAll('.rog_tile[id^="rog_tile-"]').forEach((oCard) => {
                this.destroy(oCard);
            });

            // This function is refreshUI compatible
            let cardIds = this.gamedatas.tiles.map((card) => {
                if (!$(`rog_tile-${card.id}`)) {
                    this.addTile(card);
                }
        
                let o = $(`rog_tile-${card.id}`);
                if (!o) return null;
        
                let container = this.getTileContainer(card);
                if (o.parentNode != $(container)) {
                    dojo.place(o, container);
                }
                return card.id;
            });
            this._counters['deckSize1'] = this.createCounter('rog_deck_size-1',this.gamedatas.deckSize.era1);
            this._counters['deckSize2'] = this.createCounter('rog_deck_size-2',this.gamedatas.deckSize.era2);
            
        },
        tplShoreSpace(position) {
            return `<div id='rog_shore_space-${position}' class='rog_shore_space' data-pos='${position}'></div>`;
        },
    
        addTile(tile, location = null) {
            debug('addTile',tile);
            let divId = `rog_tile-${tile.id}`;
            if ($(divId)) return $(divId);
            let o = this.place('tplTile', tile, location == null ? this.getTileContainer(tile) : location);
            let tooltipDesc = this.getTileTooltip(tile);
            if (tooltipDesc != null) {
                this.addCustomTooltip(o.id, tooltipDesc.map((t) => this.formatString(t)).join('<br/>'));
            }
    
            return o;
        },
        getTileTooltip(tile) {
            let cardDatas = tile;
            let typeName = '';
            let subtype = tile.subtype;
            if(cardDatas.buildingType){
                //building
                typeName = BUILDING_TYPES[cardDatas.buildingType];
            }
            let div = this.tplTile(cardDatas,'_tmp');
            return [`<div class='rog_tile_tooltip' data-subtype='${subtype}'><h1>${typeName}</h1>${div}</div>`];
        },
        tplTile(tile, prefix ='') {
            return `<div class="rog_tile" id="rog_tile${prefix}-${tile.id}" data-id="${tile.id}" data-type="${tile.type}" data-subtype="${tile.subtype}">
                </div>`;
        },
        addMasteryCardHolder(tile) {
            debug("addMasteryCardHolder",tile);
            let divId = `rog_tile_holder-${tile.id}`;
            if ($(divId)) return $(divId);
            let elt = this.place('tplMasteryCardHolder', tile, $('rog_mastery_cards'));
            return elt;
        },
        tplMasteryCardHolder(tile) {
            return `<div class="rog_tile_holder" id="rog_tile_holder-${tile.id}"></div>`;
        },
    
        getTileContainer(tile) {
            if (tile.location == TILE_LOCATION_SCORING) {
                return $(`rog_scoring_tile-${tile.pos}`);
            }
            if (tile.location == TILE_LOCATION_MASTERY_CARD) {
                let holder = this.addMasteryCardHolder(tile);
                if( holder){
                    return holder.id;
                }
            }
            if (tile.location == TILE_LOCATION_BUILDING_ROW) {
                return $(`rog_building_slot-${tile.pos}`);
            }
            if (tile.location == TILE_LOCATION_BUILDING_DECK_ERA_1) {
                return $(`rog_building_era-1`);
            }
            if (tile.location == TILE_LOCATION_BUILDING_DECK_ERA_2) {
                return $(`rog_building_era-2`);
            }
            if (tile.location == TILE_LOCATION_BUILDING_SHORE) {
                return $(`rog_shore_space-${tile.pos}`);
            }
    
            console.error('Trying to get container of a tile', tile);
            return 'game_play_area';
        },

        ////////////////////////////////////////////////////////
        //  __  __                 _
        // |  \/  | ___  ___ _ __ | | ___  ___
        // | |\/| |/ _ \/ _ \ '_ \| |/ _ \/ __|
        // | |  | |  __/  __/ |_) | |  __/\__ \
        // |_|  |_|\___|\___| .__/|_|\___||___/
        //                  |_|
        //////////////////////////////////////////////////////////

        setupMeeples() {
            // This function is refreshUI compatible
            //destroy previous meeples
            document.querySelectorAll('.rog_meeple[id^="rog_meeple-"]').forEach((e) => {
                this.destroy(e);
            });
            let eltIds = this.gamedatas.meeples.map((meeple) => {
                if (!$(`rog_meeple-${meeple.id}`)) {
                    this.addMeeple(meeple);
                }
        
                let o = $(`rog_meeple-${meeple.id}`);
                if (!o) return null;
        
                let container = this.getMeepleContainer(meeple);
                if (o.parentNode != $(container)) {
                    dojo.place(o, container);
                }
                return meeple.id;
            });
        },
    
        addMeeple(meeple, location = null) {
            debug('addMeeple',meeple);
            if ($('rog_meeple-' + meeple.id)) return;
    
            let o = this.place('tplMeeple', meeple, location == null ? this.getMeepleContainer(meeple) : location); 
            return o;
        },
    
    
        tplMeeple(meeple, prefix ='') {
            const PERSONAL = [MEEPLE_TYPE_CLAN_MARKER];
            let color = PERSONAL.includes(meeple.type) ? ` data-color="${this.getPlayerColor(meeple.pId)}" data-pId="${meeple.pId}" ` : '';
            return `<div class="rog_meeple" id="rog_meeple${prefix}-${meeple.id}"
                 data-id="${meeple.id}" 
                 ${color}
                 data-type="${meeple.type}">
                </div>`;
        },
    
        getMeepleContainer(meeple) {
            let locationParts = meeple.location.split('-');
            if (locationParts[0] == 'tile') {//MEEPLE_LOCATION_TILE
                // on tile
                return $(`rog_tile-${locationParts[1]}`);
            }
    
            console.error('Trying to get container of a meeple', meeple);
            return 'game_play_area';
        },


   });
});
//# sourceURL=riverofgold.js