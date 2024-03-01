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

    const CARD_LOCATION_DELIVERED = 'dd';
    const CARD_LOCATION_HAND = 'h';
    
    return declare("bgagame.riverofgold", [customgame.game], {
        constructor: function(){
            debug('riverofgold constructor');

            // Fix mobile viewport (remove CSS zoom)
            this.default_viewport = 'width=800';

            this._counters = {};
            
            this._notifications = [
                ['giveMoney', 1300],
                ['spendMoney', 1300],
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
            
            this.setupPlayers();
            this.setupInfoPanel();
            this.setupCards();
            
            debug( "Ending specific game setup" );

            this.inherited(arguments);
        },
        
        getSettingsConfig() {
            return {
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
                handWidth: {
                  default: 100,
                  name: _('Hand width'),
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
                deliveredWidth: {
                  default: 100,
                  name: _('Delivered cards width'),
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
        
        onChangeBoardWidthSetting(val) {
            this.updateLayout();
        },
        onChangeHandWidthSetting(val) {
            document.documentElement.style.setProperty('--rog_hand_scale', val/100);
            this.updateLayout();
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
            dojo.empty('rog_select_piece_container');
        },

        onEnteringStatePlayerTurn(args){
            debug('onEnteringStatePlayerTurn', args);

            this.addPrimaryActionButton(`btnBuild`, _('Build') , () =>  { this.takeAction('actBuild'); });
            this.addPrimaryActionButton(`btnSail`, _('Sail') , () =>  { this.takeAction('actSail'); });
            this.addPrimaryActionButton(`btnDeliver`, _('Deliver') , () =>  { this.takeAction('actDeliver'); });
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
 
        notif_spendMoney(n) {
            debug('Notif: spending money', n);
            this.gainPayMoney(n.args.player_id, -n.args.n);
        },
    
        notif_giveMoney(n) {
            debug('Notif: gaining money', n);
            this.gainPayMoney(n.args.player_id, n.args.n);
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
                if(isCurrent) this.place('tplPlayerHand', player, 'rog_players_boards', 'first');
                this.place('tplPlayerDeliveredCards', player, 'rog_players_deliveries');
                
                let pId = player.id;
                this._counters[pId] = {
                    money: this.createCounter(`rog_counter_${pId}_money`, player.money),
                };
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
            if (this.isFastMode()) {
                this._counters[pId]['money'].incValue(n);
                return Promise.resolve();
            }
    
            let elem = `<div id='rog_money_animation'>
                ${Math.abs(n)}
                <div class="rog_icon_container rog_icon_container_money">
                    <div class="rog_icon rog_icon_money"></div>
                </div>
                </div>`;
            $('page-content').insertAdjacentHTML('beforeend', elem);
    
            if (n > 0) {
                return this.slide('rog_money_animation', `rog_counter_${pId}_money`, {
                    from: targetSource || this.getVisibleTitleContainer(),
                    destroy: true,
                    phantom: false,
                    duration: 1200,
                }).then(() => this._counters[pId]['money'].incValue(n));
            } else {
                this._counters[pId]['money'].incValue(n);
                return this.slide('rog_money_animation', targetSource || this.getVisibleTitleContainer(), {
                    from: `rog_counter_${pId}_money`,
                    destroy: true,
                    phantom: false,
                    duration: 1200,
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
            document.querySelectorAll('.rog_card[id^="card-"]').forEach((oCard) => {
                if (!cardIds.includes(parseInt(oCard.getAttribute('data-id')))) {
                    this.destroy(oCard);
                }
            });
        },
    
        addCard(card, location = null) {
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
            return [`<div class='rog_card_tooltip'><h4>${_(cardDatas.title)}</h4>${desc}${div}</div>`];
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
                    <div class='player-name' style='color:#${player.color}'>${player.name}</div>
                    <div class='rog_cards_delivered' id='rog_cards_delivered-${player.id}'></div>
                </div>
            </div>`;
        },


   });
});
//# sourceURL=riverofgold.js