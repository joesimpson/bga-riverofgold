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

    const REGION_1 =  1;
    const REGION_2 =  2;
    const REGION_3 =  3;
    const REGION_4 =  4;
    const REGION_5 =  5;
    const REGION_6 =  6;
    const REGIONS = [
       REGION_1,
       REGION_2,
       REGION_3,
       REGION_4,
       REGION_5,
       REGION_6,
    ];

    const NB_SHORE_SPACES = 30;
    const NB_RIVER_SPACES = 14;

    const NB_MAX_MONEY = 25;
    const NB_MAX_RESOURCE = 6;
    const NB_MAX_INLFUENCE = 18;

    const CUSTOMER_TYPE_ARTISAN =  1;
    const CUSTOMER_TYPE_ELDER =    2;
    const CUSTOMER_TYPE_MERCHANT = 3;
    const CUSTOMER_TYPE_MONK =     4;
    const CUSTOMER_TYPE_NOBLE =    5;

    const CARD_MERCHANT_1 = 13;
    const CARD_MERCHANT_2 = 14;
    const CARD_MERCHANT_3 = 15;
    const CARD_MERCHANT_4 = 16;
    const CARD_MERCHANT_5 = 17;
    const CARD_MERCHANT_6 = 18;
    const CARD_NOBLE_1 = 25;
    const CARD_NOBLE_2 = 26;
    const CARD_NOBLE_3 = 27;
    const CARD_NOBLE_4 = 28;
    const CARD_NOBLE_5 = 29;
    const CARD_NOBLE_6 = 30;

    const MONK_TYPE_OWN_BUILDING = 1;
    const MONK_TYPE_OPPONENT_BUILDING = 2;

    const CARD_TYPE_CUSTOMER = 1;
    const CARD_TYPE_CLAN_PATRON = 2;

    const TILE_TYPE_SCORING = 1;
    const TILE_TYPE_BUILDING = 2;
    const TILE_TYPE_MASTERY_CARD = 3;

    const MASTERY_TYPE_AIR    = 1; 
    const MASTERY_TYPE_COURTS = 2;
    const MASTERY_TYPE_EARTH  = 3;
    const MASTERY_TYPE_FIRE   = 4;
    const MASTERY_TYPE_VOID   = 5;
    const MASTERY_TYPE_WATER  = 6;

    const TILE_LOCATION_SCORING = 's';
    const TILE_LOCATION_MASTERY_CARD = 'm';
    const TILE_LOCATION_BUILDING_DECK = 'bd';
    const TILE_LOCATION_BUILDING_DECK_ERA_1 = TILE_LOCATION_BUILDING_DECK+'1';
    const TILE_LOCATION_BUILDING_DECK_ERA_2 = TILE_LOCATION_BUILDING_DECK+'2';
    const TILE_LOCATION_BUILDING_ROW ='br';
    const TILE_LOCATION_BUILDING_SHORE = 'sh';

    const CARD_LOCATION_DELIVERED = 'dd';
    const CARD_LOCATION_HAND = 'h';
    const CARD_CLAN_LOCATION_ASSIGNED = 'clans_assigned';

    const PATRON_MASTER_ENGINEER = 1;
    const PATRON_TRADER          = 2;
    const PATRON_SON_OF_STORM    = 3;
    const PATRON_PRIESTESS       = 4;
    const PATRON_IRON_CRANE      = 5;
    const PATRON_DARLING         = 6;
    const PATRON_GOVERNOR        = 7;
    const PATRON_LADY            = 8;

    const RESOURCE_TYPE_SILK = 1;
    const RESOURCE_TYPE_POTTERY = 2;
    const RESOURCE_TYPE_RICE = 3;
    const RESOURCE_TYPE_MOON = 4;
    const RESOURCE_TYPE_SUN = 5;
    const RESOURCE_TYPE_MONEY = 6;
    const BONUS_TYPE_POINTS = 20;
    const BONUS_TYPE_CHOICE = 21;
    const BONUS_TYPE_INFLUENCE = 22;
    const BONUS_TYPE_DRAW = 23;
    const BONUS_TYPE_UPGRADE_SHIP = 24;
    const BONUS_TYPE_SECOND_MARKER_ON_BUILDING = 25;
    const BONUS_TYPE_SECOND_MARKER_ON_OPPONENT = 26;
    const BONUS_TYPE_SELL_GOODS = 28;
    const BONUS_TYPE_REFILL_HAND = 29;
    const BONUS_TYPE_MONEY_PER_PORT = 30;
    const BONUS_TYPE_MONEY_PER_MANOR = 31;
    const BONUS_TYPE_MONEY_PER_MARKET = 32;
    const BONUS_TYPE_MONEY_PER_SHRINE = 33;
    const BONUS_TYPE_MONEY_PER_CUSTOMER = 34;
    const BONUS_TYPE_SET_DIE = 35;
    const RESOURCES = [
        0,
        'silk',//RESOURCE_TYPE_SILK
        'pottery',//RESOURCE_TYPE_POTTERY
        'rice',//RESOURCE_TYPE_RICE
        'favor_total',//RESOURCE_TYPE_MOON
        'favor',//RESOURCE_TYPE_SUN
        'money',//RESOURCE_TYPE_MONEY
    ];

    const BUILDING_TYPE_PORT =     1;
    const BUILDING_TYPE_MARKET =   2;
    const BUILDING_TYPE_MANOR =    3;
    const BUILDING_TYPE_SHRINE =   4;
    
    const MEEPLE_TYPE_SHIP = 1;
    const MEEPLE_TYPE_SHIP_ROYAL = 3;
    const MEEPLE_TYPE_CLAN_MARKER = 2;
    const MEEPLE_TYPE_SCORE_MARKER = 9;

    const CLAN_CARD_BACK = 0;//Back with image
    const CLAN_CARD_FRONT = 1;

    const PREF_PLAYER_PANEL_DETAILS = 100;

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
                ['newPlayerColor', 10],
                ['giveMoney', 1300],
                ['spendMoney', 1300],
                //['draftCards', 10],
                //['draftPlayerCards', 10],
                ['giveClanCardTo', 1000],
                ['giveCardToPublic', 10],
                ['giveCardTo', 1000],
                ['deliver', 1000],
                ['discardPublic', 10],
                ['discard', 1000],
                ['giveResource', 1000],
                ['spendResource', 800],
                ['build', 1300],
                ['sail', 1300],
                ['newClanMarkers', 10],
                ['newClanMarker', 800],
                ['newBoat', 800],
                ['upgradeShip', 800],
                ['rollDie', 800],
                ['setDie', 800],
                ['gainInfluence', 1300],
                ['claimMC', 1200],
                ['scoreDeliveries', 1200],
                ['scoreInfluence', 1200],
                ['scoreElder', 1200],
                ['scoreArtisans', 1200],
                ['scoreMerchants', 1200],
                ['scoreCustomer', 1200],
                ['addPoints', 1200],
                ['scorePatron', 1200],
                ['discardBuildingRow', 500],
                ['slideBuildingRow', 600],
                ['refillBuildingRow', 800],
                ['emperorVisit', 2000],
                ['emperorReward', 1500],
                ['emperorVisitEnd', 2000],
                ['triggerLastTurn', 3000],
                ['triggerEnd', 3000],
            ];

            //Filter states where we don't want other players to display state actions
            this._activeStates = ['deliver','discardCard','draftMulti'];
            this._inactiveStates = ['draft'];
            
            this._hideNotifsWhenMultiActive = true;
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
            
            //Beware _() function is useless in constructor
            this.BUILDING_TYPES = [
                0,
                _('Port'),//BUILDING_TYPE_PORT
                _('Market'),//BUILDING_TYPE_MARKET
                _('Manor'),//BUILDING_TYPE_MANOR
                _('Shrine'),//BUILDING_TYPE_SHRINE
            ];
            this.CLANS_NAMES =  new Map([
                [1, _('Crab Clan')],
                [2, _('Mantis Clan')],
                [3, _('Crane Clan')],
                [4, _('Scorpion Clan')],
            ]);
            this.CUSTOMER_TYPES =  new Map([
                [CUSTOMER_TYPE_ARTISAN , _('Artisan')],
                [CUSTOMER_TYPE_ELDER   , _('Elder')],
                [CUSTOMER_TYPE_MERCHANT, _('Merchant')],
                [CUSTOMER_TYPE_MONK    , _('Monk')],
                [CUSTOMER_TYPE_NOBLE   , _('Noble')],
            ]);
            
            let toPreloadList = ['cards/masterycards.jpg','cards/customers.jpg'];
            this.ensureSpecificGameImageLoading(toPreloadList);

            this._counters['deckSize1'] = this.createCounter('rog_deck_size-1',this.gamedatas.deckSize.era1);
            this._counters['deckSize2'] = this.createCounter('rog_deck_size-2',this.gamedatas.deckSize.era2);
            
            this.setupTiles();
            this.setupPlayers();
            this.setupInfoPanel();
            this.setupCards();
            this.setupMeeples();

            this.addCustomTooltip('rog_score_customers', this.getScoreCustomersTooltip());
            this.addCustomTooltip('rog_era_tile_resizable', this.getEraTileTooltip());
            this.addCustomTooltip('rog_deck_size-1', `<h4>${this.fsr(_('Tiles in Era ${n} stack'), { n: 1 })}</h4>`); 
            this.addCustomTooltip('rog_deck_size-2', `<h4>${this.fsr(_('Tiles in Era ${n} stack'), { n: 2 })}</h4>`); 
            this.addCustomTooltip('rog_complete_journey', this.getCompleteJourneyTooltip());
            this.addCustomTooltip('rog_building_bonus_favor', this.getBuildingRowBonusTooltip());

            debug( "Ending specific game setup" );

            this.inherited(arguments);
        },
        
        getSettingsConfig() {
            let gameWidthScale = this.getGameZoneWidth()/1200;
            return {
                //Default scales repartition for a gameWidth of 1200 : 50,16,75
                masteryWidth: {
                  default: Math.min(Math.max(gameWidthScale *50,30),100),
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
                    default: Math.min(Math.max(gameWidthScale *16,10),50),
                  name: _('Building board size'),
                  type: 'slider',
                  sliderConfig: {
                    step: 2,
                    padding: 0,
                    range: {
                        //relative to big image dimensions
                      min: [10],
                      max: [50],
                    },
                  },
                }, 
                handWidth: {
                    default: Math.min(Math.max(gameWidthScale *75,30),200),
                  name: _('Hand cards size'),
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
                deliveredWidth: {
                  default: 70,
                  name: _('Delivered cards size'),
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
                playerPanelDetails: { type: 'pref', prefId: PREF_PLAYER_PANEL_DETAILS },
                logTileWidth: {
                  default: 50,
                  name: _('Tile width in logs'),
                  type: 'slider',
                  sliderConfig: {
                    step: 2,
                    padding: 0,
                    range: {
                      min: [20],
                      max: [66],
                    },
                  },
                }, 
                buttonsTileWidth: {
                    default: 70,
                    name: _('Tile width in buttons'),
                    type: 'slider',
                    sliderConfig: {
                      step: 2,
                      padding: 0,
                      range: {
                        min: [50],
                        max: [120],
                      },
                    },
                }, 
                deckSizeWidth: {
                    default: 100,
                    name: _('Era stack size'),
                    type: 'slider',
                    sliderConfig: {
                      step: 2,
                      padding: 0,
                      range: {
                        min: [50],
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
        onChangeDeliveredWidthSetting(val) {
            document.documentElement.style.setProperty('--rog_delivered_scale', val/100);
            this.updateLayout();
        },
        onChangeEraTileWidthSetting(val) {
            document.documentElement.style.setProperty('--rog_era_tile_holder_scale', val/100);
        },

        onChangeLogTileWidthSetting(val) {
            document.documentElement.style.setProperty('--rog_tileLogScale', val/100);
        },
        onChangeButtonsTileWidthSetting(val) {
            document.documentElement.style.setProperty('--rog_tileButtonsScale', val/100);
        },
        onChangeDeckSizeWidthSetting(val) {
            document.documentElement.style.setProperty('--rog_deckSizeScale', val/100);
        },
        onChangeDeliveredWidthSetting(val) {
            document.documentElement.style.setProperty('--rog_delivered_scale', val/100);
            this.updateLayout();
        },
       
        /** Listening for BGA user preference changes */
        onPreferenceChange(pref, newValue) {
            debug("onPreferenceChange",pref, newValue);
            pref = parseInt(pref);
            if(pref == PREF_PLAYER_PANEL_DETAILS ){
                this.adaptPlayersPanels();
            }
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
        onEnteringState(stateName, args) {
            //In this game, we don't want inactive players to see active buttons
            if (!this._inactiveStates.includes(stateName) && !this.isCurrentPlayerActive()) return;
            this.inherited(arguments);
        },
        onEnteringStateDraft(args) {
            debug('onEnteringStateDraft', args);
            this.initCardSelection(args.cards);
        },
        
        onEnteringStateDraftMulti(args) {
            debug('onEnteringStateDraftMulti', args);
            this.initCardSelection(args._private.cards);
        },

        onEnteringStatePlayerTurn(args){
            debug('onEnteringStatePlayerTurn', args);
            let possibleActions = args.a;
            this.addPrimaryActionButton(`btnSpendFavor`, _('Spend Favor') , () =>  { this.takeAction('actSpendFavor'); });
            this.addPrimaryActionButton(`btnTrade`, _('Trade') , () =>  { this.takeAction('actTrade'); });
            this.addPrimaryActionButton(`btnBuild`, _('Build') , () =>  { this.takeAction('actBuild'); });
            this.addPrimaryActionButton(`btnSail`, _('Sail') , () =>  { this.takeAction('actSail'); });
            this.addPrimaryActionButton(`btnDeliver`, _('Deliver') , () =>  { this.takeAction('actDeliver'); });

            if(!possibleActions.includes('actSpendFavor')){
                $('btnSpendFavor').classList.add('disabled');
            }
            if(!possibleActions.includes('actTrade')){
                $('btnTrade').classList.add('disabled');
            }
            if(!possibleActions.includes('actBuild')){
                $('btnBuild').classList.add('disabled');
            }
            if(!possibleActions.includes('actSail')){
                $('btnSail').classList.add('disabled');
            }
            if(!possibleActions.includes('actDeliver')){
                $('btnDeliver').classList.add('disabled');
            }
        },
        
        onEnteringStateBeforeTurn(args){
            debug('onEnteringStateBeforeTurn', args);
            this.initFavorSelection(args.p,'actBonusSetDie');
            this.addPrimaryActionButton(`btnPass`, _('Skip') , () =>  {
                this.confirmationDialog(_('Are you sure to skip and roll your die ?'), () => {
                    this.takeAction('actSkip', {});
                });
            });
        },
        onEnteringStateBonusSetDie(args){
            debug('onEnteringStateBonusSetDie', args);
            this.initFavorSelection(args.p,'actBonusSetDie');
        },
        onEnteringStateSpendFavor (args){
            debug('onEnteringStateSpendFavor', args);
            this.initFavorSelection(args.p,'actDFSelect');
        },
        onEnteringStateTrade(args){
            debug('onEnteringStateTrade', args);

            Object.values(args.p).forEach((trade) => {
                let src = Object.keys(trade.src)[0];
                let dest = Object.keys(trade.dest)[0];
                let qtySrc = Object.values(trade.src)[0];
                let qtyDest = Object.values(trade.dest)[0];
                let iconSrc = this.formatIcon(RESOURCES[src],null);
                let iconDest = this.formatIcon(RESOURCES[dest],null);
                this.addImageActionButton(`btnTrade_${src}_${dest}`, `<div class='rog_trade'>
                    <div class='rog_button_qty'>${qtySrc}</div>${iconSrc}
                    <i class="fa6 fa6-arrow-right"></i>
                    <div class='rog_button_qty'>${qtyDest}</div>${iconDest}
                </div>`, () =>  {
                    this.takeAction('actTradeSelect', {src:src,dest:dest});
                });
            });
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
                        //For hover tile display :
                        shoreSpacesDiv.querySelectorAll('.rog_shore_space.selectable').forEach((elt) => {
                            elt.dataset.type = tile.dataset.type;
                        });
                    }
                };
                this.onClick(`${tile.parentNode.id}`, callbackTileSelection);
                this.addImageActionButton(buttonId, `<div class='rog_button_building_tile_image' data-type='${tile.dataset.type}' data-id='${tileId}'></div>`, callbackTileSelection);
                $(buttonId).classList.add('rog_button_building_tile');
            });
        },
        
        onEnteringStateBonusChoice(args){
            debug('onEnteringStateBonusChoice', args);

            this.addPrimaryActionButton(`btnTrade`, _('Trade') , () =>  { this.takeAction('actTrade'); });
            if(!args.trade){
                $('btnTrade').classList.add('disabled');
            }

            let k=0;
            Object.values(args.p).forEach((bonusType) => {
                let iconBonus = this.formatIcon('bonus-'+bonusType);
                let buttonText = '';
                if(BONUS_TYPE_REFILL_HAND == bonusType) buttonText = _('Refill hand');
                if(BONUS_TYPE_SELL_GOODS == bonusType) buttonText = _('Sell goods');
                if(BONUS_TYPE_SET_DIE == bonusType) buttonText = _('Set next turn die')+"<br><div class='rog_clan_special_ability'>"+_('Clan special ability')+"</div>";
                this.addImageActionButton(`btnBonus_${k}_${bonusType}`, `${buttonText}<div class='rog_trade'>
                    ${iconBonus}
                </div>`, () =>  {
                    let confirmMessage = null;
                    if(BONUS_TYPE_DRAW == bonusType){
                        confirmMessage = this.fsr(_('Are you sure to draw ${n} cards now ?'), { n: 1 });
                    }
                    if(BONUS_TYPE_REFILL_HAND == bonusType){
                        confirmMessage = this.fsr(_('Are you sure to draw ${n} cards now ?'), { n: 2 });
                    }
                    if(confirmMessage){
                        this.confirmationDialog(confirmMessage, () => {
                            this.takeAction('actBonus', {t:bonusType});
                        });
                    }
                    else {
                        this.takeAction('actBonus', {t:bonusType});
                    }
                });
                if(BONUS_TYPE_SET_DIE == bonusType && args.cannotSetDie){
                    $(`btnBonus_${k}_${bonusType}`).classList.add('disabled');
                }
                k++;
            });

            this.addPrimaryActionButton(`btnSkip`, _('Skip') , () =>  { 
                this.confirmationDialog(_('Are you sure to skip bonuses ?'), () => {
                    this.takeAction('actSkipBonuses', {});
                });
            });
            if(!args.canSkip){
                $('btnSkip').classList.add('disabled');
            }
        },

        onEnteringStateBonusResource(args){
            debug('onEnteringStateBonusResource', args);

            Object.values(args.p).forEach((resourceType) => {
                let qtyDest = 1;
                let iconResource = this.formatIcon(RESOURCES[resourceType],null);
                this.addImageActionButton(`btnBonus_${resourceType}`, `<div class='rog_trade'>
                    <div class='rog_button_qty'>${qtyDest}</div>${iconResource}
                </div>`, () =>  {
                    this.takeAction('actBonusResource', {r:resourceType});
                });
            });
        },
        onEnteringStateBonusMoneyGood(args){
            debug('onEnteringStateBonusMoneyGood', args);

            //Same selection as previous state +1 button
        
            let qtyDest = 3;
            let resourceType = RESOURCE_TYPE_MONEY;
            let iconResource = this.formatIcon(RESOURCES[resourceType],null);
            this.addImageActionButton(`btnBonus_money_3`, `<div class='rog_trade'>
                <div class='rog_button_qty'>${qtyDest}</div>${iconResource}
            </div>`, () =>  {
                this.takeAction('actBonus3Money', {});
            });
            if(!args.money3){
                $(`btnBonus_money_3`).classList.add('disabled');
            }

            this.onEnteringStateBonusResource(args);
        },
        
        onEnteringStateBonusUpgrade(args){
            debug('onEnteringStateBonusUpgrade', args);
 
            this.selectedShipId = null; 
            let riverSpacesDiv = $(`rog_river_spaces`);
            let confirmMessage = _('Upgrade ship on river space #${n}');
            this.addPrimaryActionButton('btnConfirm', this.fsr(confirmMessage, {n:0}), () => {
                this.takeAction('actBonusUpgrade', { s: this.selectedShipId});
            }); 
            //DISABLED by default
            $(`btnConfirm`).classList.add('disabled');

            let possibleShips = args.p;
            Object.values(possibleShips).forEach((shipId) => {
                //Click SELECT ship
                this.onClick(`rog_meeple-${shipId}`, (evt) => {

                    [...riverSpacesDiv.querySelectorAll('.rog_river_space .rog_meeple')].forEach((elt) => { 
                        elt.classList.remove('selected');
                    });
                    let divShip = $(`rog_meeple-${shipId}`);
                    divShip.classList.add('selected');
                    this.selectedShipId = shipId;
                    this.selectedSpace = divShip.dataset.pos;
                    $(`btnConfirm`).classList.remove('disabled');
                    $('btnConfirm').innerHTML = this.fsr(confirmMessage, { n: this.selectedSpace });
                });
            });
        },
        
        onEnteringStateBonusSecondMarker(args){
            debug('onEnteringStateBonusSecondMarker', args);
  
            this.selectedTileId = null; 
            let shoreSpacesDiv = $(`rog_shore_spaces`);
            this.addPrimaryActionButton('btnConfirm', this.fsr(_('Confirm'), {}), () => {
                this.takeAction('actBonusSecondMarker', { t: this.selectedTileId});
            }); 
            //DISABLED by default
            $(`btnConfirm`).classList.add('disabled');

            Object.values(args.p).forEach((tileId) => {
                let tileDiv = $(`rog_tile-${tileId}`);
                this.onClick(`${tileDiv.id}`, (evt) => {
                    shoreSpacesDiv.querySelectorAll('.rog_tile').forEach((elt) => {
                        elt.classList.remove('selected');
                    });
                    tileDiv.classList.add('selected');
                    this.selectedTileId = tileId;
                    $(`btnConfirm`).classList.remove('disabled');
                });
            });
        },
        
        onEnteringStateBonusSellGoods(args){
            debug('onEnteringStateBonusSellGoods', args);
   
            Object.values(args.p).forEach((trade) => {
                let src = Object.keys(trade.src)[0];
                let dest = Object.keys(trade.dest)[0];
                let qtySrc = Object.values(trade.src)[0];
                let qtyDest = Object.values(trade.dest)[0];
                let iconSrc = this.formatIcon(RESOURCES[src],null);
                let iconDest = this.formatIcon(RESOURCES[dest],null);
                this.addImageActionButton(`btnTrade_${src}_${dest}`, `<div class='rog_trade'>
                    <div class='rog_button_qty'>${qtySrc}</div>${iconSrc}
                    <i class="fa6 fa6-arrow-right"></i>
                    <div class='rog_button_qty'>${qtyDest}</div>${iconDest}
                </div>`, () =>  {
                    this.takeAction('actSell', {src:src});
                });
            });
            this.addPrimaryActionButton('btnEnd', this.fsr(_('End selling'), {}), () => {
                this.takeAction('actStop', { });
            });
        },

        onEnteringStateSail(args){
            debug('onEnteringStateSail', args);

            this.selectedShipId = null; 
            this.selectedSpace = null; 
            let riverSpacesDiv = $(`rog_river_spaces`);
            let confirmMessage = _('Sail to river space #${n}');
            this.addPrimaryActionButton('btnConfirm', this.fsr(confirmMessage, {n:0}), () => {
                this.takeAction('actSailSelect', { s: this.selectedShipId,r: this.selectedSpace});
            }); 
            //DISABLED by default
            $(`btnConfirm`).classList.add('disabled');

            let possibleMoves = args.spaces;
            Object.keys(possibleMoves).forEach((shipId) => {
                //Click ship origin
                this.onClick(`rog_meeple-${shipId}`, (evt) => {

                    //disable confirm while we don't know destination
                    $(`btnConfirm`).classList.add('disabled');
                    $('btnConfirm').innerHTML = this.fsr(confirmMessage, { n: 0 });
                    [...riverSpacesDiv.querySelectorAll('.rog_river_space')].forEach((elt) => { 
                        elt.classList.remove('selectable'); 
                        elt.classList.remove('selected');
                    });
                    let divShip = $(`rog_meeple-${shipId}`);
                    if(divShip.classList.contains('selected')){
                        //UNSELECT
                        this.selectedShipId = null;
                        this.selectedSpace = null; 
                        divShip.classList.remove('selected');
                        return;
                    }else {
                        //SELECT
                        [...riverSpacesDiv.querySelectorAll('.rog_river_space .rog_meeple')].forEach((elt) => { 
                            elt.classList.remove('selected');
                        });
                        divShip.classList.add('selected');
                        this.selectedShipId = shipId;
                        this.selectedSpace = null; 
                    }

                    Object.values(possibleMoves[shipId]).forEach((space) => {
                        let div = $(`rog_river_space-${space}`);
                        this.onClick(`${div.id}`, (evt) => {
                            //CLICK SHIP DESTINATION
                            [...riverSpacesDiv.querySelectorAll('.rog_river_space')].forEach((elt) => { elt.classList.remove('selected');});
                            div.classList.add('selected');
                            this.selectedSpace = div.dataset.pos;
                            $(`btnConfirm`).classList.remove('disabled');
                            $('btnConfirm').innerHTML = this.fsr(confirmMessage, { n: this.selectedSpace });
                        });
                    });
                });
            });
        },

        onEnteringStateDeliver(args){
            debug('onEnteringStateDeliver', args);

            this.selectedCardId = null;
            let confirmMessage = _('Deliver to ${customer_name}');
            this.addPrimaryActionButton('btnConfirm', this.fsr(confirmMessage, {customer_name:''}), () => {
                this.takeAction('actDeliverSelect', { c: this.selectedCardId});
            }); 
            //DISABLED by default
            $(`btnConfirm`).classList.add('disabled');

            let cards = args._private.c;
            Object.values(cards).forEach((cardId) => {
                let div = $(`rog_card-${cardId}`);
                this.onClick(`${div.id}`, (evt) => {
                    [...$(`rog_player_hand-${this.player_id}`).querySelectorAll('.rog_card')].forEach((elt) => { elt.classList.remove('selected');});
                    div.classList.add('selected');
                    this.selectedCardId = cardId;
                    $(`btnConfirm`).classList.remove('disabled');
                    $('btnConfirm').innerHTML = this.fsr(confirmMessage, { customer_name: div.dataset.customer_name });
                });
            });
        },
        
        onEnteringStateDiscardCard(args){
            debug('onEnteringStateDiscardCard', args);

            this.selectedCardId = null;
            let confirmMessage = _('Discard ${customer_name}');
            this.addPrimaryActionButton('btnConfirm', this.fsr(confirmMessage, {customer_name:''}), () => {
                this.takeAction('actDiscardCard', { c: this.selectedCardId});
            }); 
            //DISABLED by default
            $(`btnConfirm`).classList.add('disabled');

            let handDiv = $(`rog_player_hand-${this.player_id}`);
            let cards = args._private.c;
            Object.values(cards).forEach((cardId) => {
                let div = $(`rog_card-${cardId}`);
                this.onClick(`${div.id}`, (evt) => {
                    [...handDiv.querySelectorAll('.rog_card')].forEach((elt) => { 
                        elt.classList.remove('selected'); 
                        elt.classList.remove('rog_selectedToDiscard');
                    });
                    div.classList.add('selected');
                    div.classList.add('rog_selectedToDiscard');
                    this.selectedCardId = cardId;
                    $(`btnConfirm`).classList.remove('disabled');
                    $('btnConfirm').innerHTML = this.fsr(confirmMessage, { customer_name: div.dataset.customer_name });
                });
            });
        },

        onEnteringStateConfirmTurn(args) {
            this.addPrimaryActionButton(`btnTrade`, _('Trade') , () =>  { this.takeAction('actTrade'); });
            if(!args.trade){
                $('btnTrade').classList.add('disabled');
            }

            this.addPrimaryActionButton('btnConfirmTurn', _('Confirm'), () => {
                this.takeAction('actConfirmTurn');
            });
        },
        
        onUpdateActivityDraftMulti: function(args)
        {
            debug( 'onUpdateActivityDraftMulti() ', args );
            if( !this.isCurrentPlayerActive() ){
                this.clearPossible();
            }
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
        notif_newPlayerColor(n) {
            debug('notif_newPlayerColor: receiving a new color', n);
            this.refreshPlayerColor(n.args.player_id,n.args.player_color);
        },
        notif_giveClanCardTo(n) {
            debug('notif_giveClanCardTo: receiving a new clan card', n);
            if (!$(`rog_clan_card-${n.args.card.id}`)) this.addClanCard(n.args.card, this.getVisibleTitleContainer());
            let pid = n.args.player_id;
            let rog_player_clan_panel =  `rog_player_clan_panel-${pid}`;
            this.slide(`rog_clan_card-${n.args.card.id}`, rog_player_clan_panel, {
                from: this.getVisibleTitleContainer(),
                destroy: true,
                phantom: false,
            }).then( () => {
                //Re add after destroy
                this.addClanCard(n.args.card, this.getCardContainer(n.args.card));
                if($(rog_player_clan_panel).querySelector(`.rog_icon_clan-${n.args.card.clan}`)) return;
                let clanIconDiv = this.formatIcon('clan-'+n.args.card.clan,this.CLANS_NAMES.get(n.args.card.clan));
                dojo.place(clanIconDiv,rog_player_clan_panel,'first');
            });
        },
        ////When draft active + waiting screen, we need to update cards
        //notif_draftCards(n) {
        //    debug('notif_draftCards', n);
        //    Object.values(n.args.cards).forEach((card) => {
        //        this.gamedatas.cards.push(card);
        //        this.addClanCard(card, $('rog_select_piece_container'));
        //    });
        //},
        ////When draft multiactive + waiting screen, we need to update cards
        //notif_draftPlayerCards(n) {
        //    debug('notif_draftPlayerCards', n);
        //    Object.values(n.args.cards).forEach((card) => {
        //        this.gamedatas.cards.push(card);
        //        this.addClanCard(card, $('rog_select_piece_container'));
        //    });
        //},
        notif_giveCardToPublic(n) {
            debug('notif_giveCardToPublic', n);
            //We want the text on top
        },
        notif_giveCardTo(n) {
            debug('notif_giveCardTo: receiving a new card', n);
            if (!$(`rog_card-${n.args.card.id}`)) this.addCard(n.args.card, this.getVisibleTitleContainer());
            this.slide(`rog_card-${n.args.card.id}`, this.getCardContainer(n.args.card));
        },
        notif_deliver(n) {
            debug('notif_deliver: a player shows a card to all !', n);
            let card = n.args.card;
            let div = $(`rog_card-${card.id}`);
            let oldParent = null;
            if (!div) this.addCard(card, this.getVisibleTitleContainer());
            else oldParent = div.parentNode.parentNode;//rog_customer_holder
            this.slide(`rog_card-${card.id}`, this.getCardContainer(card)).then(() =>{
                if(oldParent && oldParent.classList.contains("rog_customer_card_resizeable")) this.destroy( $(`${oldParent.id}`));
            });
            this._counters[card.pId].customers[card.customerType].incValue(+1);
        },
        notif_discardPublic(n) {
            debug('notif_discardPublic', n);
            //We want the text on top
        },
        notif_discard(n) {
            debug('notif_discard: discarding a private card', n);
            let div = $(`rog_card-${n.args.card.id}`);
            if (!div) return;
            let oldParent = div.parentNode.parentNode;//rog_customer_holder
            this.slide(`rog_card-${n.args.card.id}`, this.getVisibleTitleContainer(), {
                destroy: true,
                phantom: false,
                duration: 800,
            }).then(() =>{
                if(oldParent.classList.contains("rog_customer_card_resizeable")) this.destroy( $(`${oldParent.id}`));
            });
        },
        notif_giveResource(n) {
            debug('notif_giveResource: receiving new resources', n);
            let targetSource = null;
            if(n.args.tile_id) {
                let fromDiv = `rog_tile-${n.args.tile_id}`;
                if ($(fromDiv)){
                    targetSource = $(fromDiv);
                }
            }
            if(n.args.shore_space_pos) {
                let fromDiv = `rog_shore_space-${n.args.shore_space_pos}`;
                if ($(fromDiv)){
                    targetSource = $(fromDiv);
                }
            }
            this.gainPayResource(n.args.player_id, RESOURCES[n.args.res_type], n.args.n,targetSource);
        },
        notif_spendResource(n) {
            debug('notif_spendResource: receiving new resources', n);
            this.gainPayResource(n.args.player_id, RESOURCES[n.args.res_type], -n.args.n);
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
            let buildingType = n.args.tile.buildingType;
            this.slide(`rog_tile-${n.args.tile.id}`, this.getTileContainer(n.args.tile), {  
                from: fromDiv.id, 
                phantom: false,
            }).then( ()=> {
                this._counters[n.args.player_id].buildings[buildingType].incValue(1);
            });
        },
        notif_newClanMarkers(n) {
            debug('notif_newClanMarkers', n);
            //We want the text on top
        },
        notif_newClanMarker(n) {
            debug('notif_newClanMarker', n);
            let divMeeple = this.addMeeple(n.args.meeple, this.getVisibleTitleContainer());
            this.slide(divMeeple.id, this.getMeepleContainer(n.args.meeple), { }).then(() => {
                //this.notifqueue.setSynchronousDuration(100);
            }).then( ()=> {
                let buildingType = n.args.buildingType;
                if(buildingType){
                    this._counters[n.args.player_id].buildings[buildingType].incValue(1);
                }
            });
        },
        notif_newBoat(n) {
            debug('notif_newBoat', n);
            if (!$(`rog_meeple-${n.args.meeple.id}`)) this.addMeeple(n.args.meeple, this.getVisibleTitleContainer());
            this.slide(`rog_meeple-${n.args.meeple.id}`, this.getMeepleContainer(n.args.meeple), { });
        },
        notif_upgradeShip(n) {
            debug('notif_upgradeShip', n);
            let divMeeple = this.addMeeple(n.args.meeple, this.getVisibleTitleContainer());
            divMeeple.dataset.type = n.args.meeple.type;
            this.slide(divMeeple.id, this.getMeepleContainer(n.args.meeple), {  
                from: this.getVisibleTitleContainer(), 
                phantom: false,
            }).then( ()=> {
                let tooltipDesc = this.getMeepleTooltip(n.args.meeple);
                if (tooltipDesc != null) {
                    this.addCustomTooltip(divMeeple, tooltipDesc);
                }
            });
        },
        
        notif_sail(n) {
            debug('notif_sail: ship is moved', n);
            let ship = n.args.ship;
            let divShip = $(`rog_meeple-${ship.id}`);
            let fromDiv = divShip.parentNode;
            this.slide(divShip.id, this.getMeepleContainer(ship), {  
                from: fromDiv.id, 
                phantom: false,
            }).then( ()=> { });
        },
        notif_setDie(n) {
            debug('notif_setDie', n);
            this.updatePlayerDieFace(n.args.player_id,n.args.die_value,true);
        },
        notif_rollDie(n) {
            debug('notif_rollDie', n);
            this.updatePlayerDieFace(n.args.player_id,n.args.die_value,true);
        },
        notif_gainInfluence(n) {
            debug('notif_gainInfluence', n);
            let region = n.args.region;
            let currentPos = $(`rog_meeple-${n.args.meeple.id}`).parentNode;
            this.slide(`rog_meeple-${n.args.meeple.id}`, this.getMeepleContainer(n.args.meeple), {  
                from: currentPos,
                phantom: false,
            }).then( ()=> {
                this._counters[n.args.player_id].influence[region].toValue(n.args.n2);
            });
        },
        notif_addPoints(n) {
            debug('notif_addPoints : new score', n);
            this.gainPoints(n.args.player_id,n.args.n);
        },
        notif_claimMC(n) {
            debug('notif_claimMC : new score after mastery card', n);
            this.gainPoints(n.args.player_id,n.args.n,$(`rog_tile-${n.args.tile_id}`));
        },
        notif_scoreDeliveries(n) {
            debug('notif_scoreDeliveries', n);
            this.gainPoints(n.args.player_id,n.args.n);
        },
        notif_scoreInfluence(n) {
            debug('notif_scoreInfluence : new score for influence track', n);
            this.gainPoints(n.args.player_id,n.args.n,$(`rog_tile-${n.args.tile_id}`));
        },
        notif_scoreElder(n) {
            debug('notif_scoreElder : new score for influence track by Elder', n);
            this.gainPoints(n.args.player_id,n.args.n,$(`rog_tile-${n.args.tile_id}`));
        },
        notif_scoreArtisans(n) {
            debug('notif_scoreArtisans', n);
            this.gainPoints(n.args.player_id,n.args.n);
        },
        notif_scoreMerchants(n) {
            debug('notif_scoreMerchants', n);
            this.gainPoints(n.args.player_id,n.args.n);
        },
        notif_scoreCustomer(n) {
            debug('notif_scoreCustomer', n);
            this.gainPoints(n.args.player_id,n.args.n,$(`rog_card-${n.args.card_id}`));
        },
        notif_scorePatron(n) {
            debug('notif_scorePatron', n);
            this.gainPoints(n.args.player_id,n.args.n,$(`rog_clan_card-${n.args.card_id}`));
        },
        notif_discardBuildingRow(n) {
            debug('notif_discardBuildingRow: building tile discarded from building row', n);
            let tileDivId = `rog_tile-${n.args.tile.id}`;
            if ($(tileDivId)){
                this.slide(tileDivId, this.getVisibleTitleContainer(), { phantom: false, destroy: true}).then( ()=> {
                }); 
            }
        },
        notif_slideBuildingRow(n) {
            debug('notif_slideBuildingRow: building tiles slide on building row', n);
            Promise.all(
                [...$(n.args.tiles)].map((data, i) => {
                    let tile = data.tile;
                    let tileDivId = `rog_tile-${tile.id}`;
                    if (!$(tileDivId)) return;
                    let tileDiv = $(tileDivId);
                    let fromDiv = `rog_building_slot-${data.from}`;//tileDiv.parentNode.id;
                    return this.wait(200 * i).then(
                        () => this.slide(tileDivId, this.getTileContainer(tile), { from: fromDiv, phantom: false,}).then( ()=> {
                            tileDiv.dataset.pos = tile.pos;
                        })
                    );
                })
            ).then(() => {
                //this.notifqueue.setSynchronousDuration(this.isFastMode() ? 0 : 10);
            });
        },
        notif_refillBuildingRow(n) {
            debug('notif_refillBuildingRow: building tile moved from building board', n);
            let tileDivId = `rog_tile-${n.args.tile.id}`;
            if (!$(tileDivId)) this.addTile(n.args.tile, this.getVisibleTitleContainer());
            this.slide(tileDivId, this.getTileContainer(n.args.tile), { phantom: false,}).then( ()=> {
                this.animationBlink2Times(tileDivId);
            });
            //Era 1/2 stack top tile is updated :
            [n.args.era1, n.args.era2,].forEach((eraTile) => {
                if(!eraTile) return;
                let tileEraDivId = `rog_tile-${eraTile.id}`;
                if (!$(tileEraDivId)) {
                    /*No need to slide to represent a moving piece, because in physical game, this is a stack of pieces
                    this.addTile(eraTile, this.getVisibleTitleContainer());
                    this.slide(tileEraDivId, this.getTileContainer(eraTile), { phantom: false,}).then( ()=> {
                        this.animationBlink2Times(tileEraDivId);
                    });
                    */
                    this.addTile(eraTile, this.getTileContainer(eraTile));
                }
            }); 
            this._counters['deckSize1'].toValue(n.args.deckSize.era1);
            this._counters['deckSize2'].toValue(n.args.deckSize.era2);

        },
        notif_emperorVisit(n) {
            debug('notif_emperorVisit: new era !', n);
            this._counters['era'].toValue(n.args.era);
        },
        notif_emperorReward(n) {
            debug('notif_emperorReward', n);
            let shore_space = n.args.tile_space;
            let currentPos = null;
            if(!$(`rog_emperor_tile`)) currentPos = this.getVisibleTitleContainer();
            let divEmperor = this.addEmperorTile(shore_space);
            divEmperor.dataset.pos = shore_space;
            if(!currentPos) currentPos = divEmperor.parentNode;
            this.slide(divEmperor.id, $(`rog_shore_space-${shore_space}`), {  
                from: currentPos,
                phantom: false,
                duration: 1500,
            }).then( ()=> {
            });
        },
        notif_emperorVisitEnd(n) {
            debug('notif_emperorVisitEnd', n);
            let divEmperor = $(`rog_emperor_tile`);
            if(divEmperor) this.slide(divEmperor.id, this.getVisibleTitleContainer(), {  
                phantom: false,
                destroy:true,
            });
        },
        notif_triggerLastTurn(n) {
            debug('notif_triggerLastTurn', n);
            $('rog_end_warning').classList.remove('rog_nodisplay');
        },
        notif_triggerEnd(n) {
            debug('notif_triggerEnd', n);
            $('rog_end_warning').classList.remove('rog_nodisplay');
        },
        
        ///////////////////////////////////////////////////
        notif_clearTurn(n) {
            debug('notif_clearTurn: restarting turn/step', n);
            this.cancelLogs(n.args.notifIds);
        },
        notif_refreshUI(n) {
            debug('notif_refreshUI: refreshing UI', n);
            this.refreshPlayersDatas(n.args.datas['players']);
            ['cards', 'tiles', 'meeples','deckSize','era'].forEach((value) => {
                this.gamedatas[value] = n.args.datas[value];
            });

            this._counters['era'].toValue(this.gamedatas.era);
    
            //keep hand untouched, another notif will take care about it
            this.setupCards(true);
            this.setupTiles();
            this.setupMeeples();
    
            this.forEachPlayer((player) => {
                let pId = player.id;
                this.refreshPlayerColor(pId,player.color);
                if(!player.clan){//should not be necessary except in DEV/DEBUG
                    this.empty(`rog_player_clan_panel-${pId}`);
                }
                this.scoreCtrl[pId].toValue(player.score);
                this._counters[pId].money.toValue(player.money);
                this._counters[pId].silk.toValue(player.silk);
                this._counters[pId].pottery.toValue(player.pottery);
                this._counters[pId].rice.toValue(player.rice);
                this._counters[pId].favor_total.toValue(player.moon);
                this._counters[pId].favor.toValue(player.sun);
                this.updatePlayerDieFace(pId,player.die);
                this._counters[pId].buildings[BUILDING_TYPE_PORT    ].toValue(player.buildings[BUILDING_TYPE_PORT]);
                this._counters[pId].buildings[BUILDING_TYPE_MARKET  ].toValue(player.buildings[BUILDING_TYPE_MARKET]);
                this._counters[pId].buildings[BUILDING_TYPE_MANOR   ].toValue(player.buildings[BUILDING_TYPE_MANOR]);
                this._counters[pId].buildings[BUILDING_TYPE_SHRINE  ].toValue(player.buildings[BUILDING_TYPE_SHRINE]);
                Object.values(REGIONS).forEach((region) =>{
                    this._counters[pId].influence[region].toValue (player.influence[region]);
                });
                this.CUSTOMER_TYPES.forEach((value, key, map) =>{
                    let customer = key;
                    this._counters[pId].customers[customer].toValue(player.customers[customer]);
                });
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
        getGameZoneWidth() {
            return $('rog_main_zone').getBoundingClientRect()['width'];
        },
    
        updateLayout() {
            if (!this.settings) return;
            const ROOT = document.documentElement;
    
            const WIDTH = this.getGameZoneWidth();
            const BOARD_WIDTH = 2726;
    
            let widthScale = ((this.settings.boardWidth / 100) * WIDTH) / BOARD_WIDTH,
            scale = widthScale;
            ROOT.style.setProperty('--rog_board_display_scale', scale);
                    
            //const PLAYER_HAND_WIDTH = 300;
            //let remainingWidth = WIDTH - $('rog_resizable_river_board').getBoundingClientRect()['width'];
            //widthScale = ((this.settings.handWidth / 100) * remainingWidth) / PLAYER_HAND_WIDTH;
            //ROOT.style.setProperty('--rog_hand_scale', widthScale);

            //Update player deliveries zone (with their 10px margin) with remaining space :
            let maxDeliveriesWidthOnRiverRight = WIDTH -10*2 - widthScale*BOARD_WIDTH;
            let maxDeliveriesWidthOnRiverBottom = WIDTH -10*2;
            let maxDeliveriesWidth = (maxDeliveriesWidthOnRiverRight>400) ? maxDeliveriesWidthOnRiverRight : maxDeliveriesWidthOnRiverBottom;
            [...document.querySelectorAll(".rog_player_delivered_resizable")].forEach((zone) => {
                zone.style.setProperty('max-width', `${maxDeliveriesWidth}px`);
            });
        },
        
        undoToStep(stepId) {
            this.checkAction('actRestart');
            this.takeAction('actUndoToStep', { stepId }, false);
        },
            
        clearPossible() {
            //Clear game specific remaining variables
            this.selectedTileId = null; 
            this.selectedSpace = null; 
            $(`rog_shore_spaces`).querySelectorAll('.rog_shore_space.selectable').forEach((elt) => {
                elt.removeAttribute('data-type');
            });
            this.empty("rog_select_piece_container");
            
            this.inherited(arguments);
        },

        initCardSelection(cards) {
            let selectedCard = null;
            let confirmMessage = _('Confirm ${patron_name}');
            this.addPrimaryActionButton('btnConfirm', this.fsr(confirmMessage, {patron_name:''}), () => {
                this.takeAction('actTakeCard', { c: selectedCard });
            }); 
            //DISABLED by default
            $(`btnConfirm`).classList.add('disabled');

            Object.values(cards).forEach((card) => {
                this.addClanCard(card, $('rog_select_piece_container'));
                if (this.isCurrentPlayerActive()) {
                    this.onClick(`rog_clan_card-${card.id}`, () => {
                        if (selectedCard){
                            $(`rog_clan_card-${selectedCard}`).classList.remove('selected');
                            $(`rog_clan_card-${selectedCard}`).dataset.side = CLAN_CARD_BACK;
                        }
                        selectedCard = card.id;
                        $(`rog_clan_card-${selectedCard}`).classList.add('selected');
                        $(`rog_clan_card-${selectedCard}`).dataset.side = CLAN_CARD_FRONT;
                        $('btnConfirm').innerHTML = this.fsr(confirmMessage, { patron_name: _(card.name) });
                        $(`btnConfirm`).classList.remove('disabled');
                    });
                }
            });
        },

        initFavorSelection(possibles,actionName) {
            debug('initFavorSelection', possibles);
            Object.values(possibles).forEach((possible) => {
                let dieFace = possible.face;
                let cost = possible.cost;
                let iconFace = this.formatIcon("die_face-"+dieFace,dieFace);
                let iconCost = this.formatIcon("favor",cost);
                this.addImageActionButton(`btnDF_${dieFace}`, 
                    `<div class='rog_trade'>
                        ${iconCost}
                        <i class="fa6 fa6-arrow-right"></i>
                        ${iconFace}
                    </div>`,
                    () =>  {
                        this.takeAction(actionName, {d:dieFace});
                    }
                );
            });
        },
        getScoreCustomersTooltip() {
            let title = _('Final scoring for number of customers you have delivered to');
            let titleLine1 = _('Number of Customers');
            let titleLine2 = _('Score');
            let table = `<table id="rog_score_customers_table">
                <tbody>
                    <tr>
                        <th>${titleLine1}</th>
                        <td>1</td>
                        <td>2</td>
                        <td>3</td>
                        <td>4</td>
                        <td>5</td>
                        <td>6+</td>
                    </tr>
                    <tr>
                        <th>${titleLine2}</th>
                        <td>2</td>
                        <td>5</td>
                        <td>6</td>
                        <td>14</td>
                        <td>20</td>
                        <td>27</td>
                    </tr>
                </tbody>
                </table>`;
            return `<div class='rog_tooltip'><h2>${title}</h2>${table}</div>`;
        },
        
        getCompleteJourneyTooltip() {
            let title = _("Completing your journey");
            let textLine1 = _("Discard the tile at the end of the building row");
            let textLine2 = _("Gain 3 Koku or 1 of any trade good");
            let textLine3 = _("Gain all your merchants bonuses");
            let table = `<table id="rog_complete_journey_table">
                <tbody>
                    <tr>
                        <td>${textLine1}</td>
                    </tr>
                    <tr>
                        <td>${textLine2}</td>
                    </tr>
                    <tr>
                        <td>${textLine3}</td>
                    </tr>
                </tbody>
                </table>`;
            return `<div class='rog_tooltip'><h2>${title}</h2>${table}</div>`;
        },
        getBuildingRowBonusTooltip() {
            let text = _("If you build the row's end tile, immediately gain 1 divine favor");
            return `<div class='rog_tooltip'>${text}</div>`;
        },
        refreshPlayersDatas(players){
            debug("refreshPlayersDatas()",players);
            //Erasing this array would erase some BGA datas (ex color_back)-> we should erase only datas in parameter array
            //this.gamedatas.players = players;
            Object.values(players).forEach((player) => {
                let pid = player.id;
                for (const property in player) {
                    this.gamedatas.players[pid][property] = player[property];
                }
            });
        },

        ////////////////////////////////////////////////////////////
        // _____                          _   _   _
        // |  ___|__  _ __ _ __ ___   __ _| |_| |_(_)_ __   __ _
        // | |_ / _ \| '__| '_ ` _ \ / _` | __| __| | '_ \ / _` |
        // |  _| (_) | |  | | | | | | (_| | |_| |_| | | | | (_| |
        // |_|  \___/|_|  |_| |_| |_|\__,_|\__|\__|_|_| |_|\__, |
        //                                                 |___/
        ////////////////////////////////////////////////////////////
        /**
         * Format log strings (alias fsr)
         *  @Override
         */
        format_string_recursive(log, args) {
            try {
            if (log && args && !args.processed) {
                args.processed = true;

                log = this.formatString(_(log)); 
                let koku = 'koku';
                if(koku in args) {
                    args.koku = this.formatIcon('money',null);
                }
                let points = 'points';
                if(points in args) {
                    args.points = this.formatIcon('score',null);
                }
                let res_icon = 'res_icon';
                if(res_icon in args) {
                    args.res_icon = this.formatIcon(RESOURCES[args.res_type],null);
                }
                let influence = 'influence';
                if(influence in args) {
                    args.influence = this.formatIcon('influence',args.n2);
                }
                let region_icon = 'region_icon';
                if(region_icon in args) {
                    args.region_icon = this.formatIcon('influence-'+args.region,null);
                }
                let building_tile = 'building_tile';
                if(building_tile in args) {
                    //args.building_tile = this.formatIcon('tile_log',args.n2);
                    args.building_tile = this.tplTile(args.tile,'_log');
                }
                let die_face = 'die_face';
                if(die_face in args) {
                    args.die_face = this.formatIcon('die_face-'+args.die_face);
                }
                let ship_type = 'ship_type';
                if(ship_type in args) {
                    args.ship_type = this.formatIcon("ship-"+args.ship_type);
                }
                let bonus_icon = 'bonus_icon';
                if(bonus_icon in args) {
                    args.bonus_icon = this.formatIcon('bonus-'+args.bonus_icon);
                }
            }
            } catch (e) {
                console.error(log, args, 'Exception thrown', e.stack);
            }

            return this.inherited(arguments);
        },
        formatIcon(name, n = null) {
            let type = name;
            let text = n == null ? '' : `<span class='rog_icon_qty'>${n}</span>`;
            return `<div class="rog_icon_container rog_icon_container_${type}">
                <div class="rog_icon rog_icon_${type}">${text}</div>
                </div>`;
        },
        
        formatString(str) {
            //debug('formatString', str);
            return str;
        },
        formatReward(rewardType, quantity) {
            debug("formatReward",rewardType, quantity);
            switch(rewardType)
            {
                case RESOURCE_TYPE_RICE: return this.fsr(_('Trade good : ${n} ${element}'), {n:quantity, element:_('Rice') });
                case RESOURCE_TYPE_POTTERY: return this.fsr(_('Trade good : ${n} ${element}'), {n:quantity, element:_('Porcelain') });
                case RESOURCE_TYPE_SILK: return this.fsr(_('Trade good : ${n} ${element}'), {n:quantity, element:_('Silk') });
                case RESOURCE_TYPE_SUN: return _('Divine favor');
                case RESOURCE_TYPE_MONEY: return this.fsr(_('${n} ${element}'), {n:quantity, element:_('Koku') });
                case BONUS_TYPE_POINTS: return this.fsr(_('${n} Victory points'), {n:quantity });
                case BONUS_TYPE_CHOICE: return _('Choose any trade good');
                case BONUS_TYPE_INFLUENCE: return this.fsr(_('${n} Influence in the region it is built'), {n:quantity });
                case BONUS_TYPE_DRAW: return _('Draw and discard 1 customer card');

                case BONUS_TYPE_MONEY_PER_CUSTOMER: return this.fsr(_('Gain ${n} Koku per customer delivered to'), {n:quantity });

                case BONUS_TYPE_MONEY_PER_PORT:     return this.fsr(_('Gain ${n} Koku per ${building} you own'), {n:quantity, building:_('Port') });
                case BONUS_TYPE_MONEY_PER_MANOR:    return this.fsr(_('Gain ${n} Koku per ${building} you own'), {n:quantity, building:_('Manor') });
                case BONUS_TYPE_MONEY_PER_MARKET:   return this.fsr(_('Gain ${n} Koku per ${building} you own'), {n:quantity, building:_('Market') });
                case BONUS_TYPE_MONEY_PER_SHRINE:   return this.fsr(_('Gain ${n} Koku per ${building} you own'), {n:quantity, building:_('Shrine') });
            }
            return '';
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
            debug("setupPlayers");
            let currentPlayerNo = 1;
            let nPlayers = 0;
            this.forEachPlayer((player) => {
                let isCurrent = player.id == this.player_id;
                //BEWARE if we change color of a player during first phase of the game, some players may have the same color
                //let divPanel = $(`overall_player_board_${player.id}`).querySelector(`.player_panel_content`).id;
                //this.place('tplPlayerPanel', player, divPanel, 'after');
                let divPanel = $(`overall_player_board_${player.id}`).querySelector(`.player_panel_content`);
                divPanel.insertAdjacentHTML('beforeend', this.tplPlayerPanel(player));

                if(isCurrent) this.place('tplPlayerHand', player, 'rog_upper_zone','last');
                this.place('tplPlayerDeliveredCards', player, 'rog_players_deliveries');
                
                let pId = player.id;
                this.refreshPlayerColor(pId,player.color);
                this._counters[pId] = {
                    money: this.createCounter(`rog_counter_${pId}_money`, player.money),
                    silk: this.createCounter(`rog_counter_${pId}_silk`, player.silk),
                    pottery: this.createCounter(`rog_counter_${pId}_pottery`, player.pottery),
                    rice: this.createCounter(`rog_counter_${pId}_rice`, player.rice),
                    favor: this.createCounter(`rog_counter_${pId}_favor`, player.sun),
                    favor_total: this.createCounter(`rog_counter_${pId}_favor_total`, player.moon),
                    dieFace: this.createCounter(`rog_counter_${pId}_dieFace`, player.die),
                    buildings: [],
                    influence: [],
                    customers: [],
                };
                this.updatePlayerDieFace(pId,player.die);
                this._counters[pId].buildings[BUILDING_TYPE_PORT] = this.createCounter(`rog_counter_${pId}_port`, player.buildings[BUILDING_TYPE_PORT]);
                this._counters[pId].buildings[BUILDING_TYPE_MARKET] = this.createCounter(`rog_counter_${pId}_market`, player.buildings[BUILDING_TYPE_MARKET]);
                this._counters[pId].buildings[BUILDING_TYPE_MANOR] = this.createCounter(`rog_counter_${pId}_manor`, player.buildings[BUILDING_TYPE_MANOR]);
                this._counters[pId].buildings[BUILDING_TYPE_SHRINE] = this.createCounter(`rog_counter_${pId}_shrine`, player.buildings[BUILDING_TYPE_SHRINE]);
                
                let regions_desc = new Map([
                    [REGION_1, _('Spine of the world')],
                    [REGION_2, _('Shinomen forest')],
                    [REGION_3, _('Three Man Alliance Plain')],
                    [REGION_4, _('Kitsune Woods')],
                    [REGION_5, _('Uebe Marshes')],
                    [REGION_6, _('Earthquake Fish Bay')],
                ]); 
                Object.values(REGIONS).forEach((region) =>{
                    this._counters[pId].influence[region] = this.createCounter(`rog_counter_${pId}_influence-${region}`, player.influence[region]);
                    this.addCustomTooltip(`rog_reserve_${pId}_influence-${region}`, this.fsr(_('Influence in region ${n} : ${region_desc}'),{n:region,region_desc:regions_desc.get(region)}));
                });
                this.CUSTOMER_TYPES.forEach((value, key, map) =>{
                    //let customerName = this.CUSTOMER_TYPES.get(customer);
                    let customer = key;
                    let customerName = value;
                    this._counters[pId].customers[customer] = this.createCounter(`rog_counter_${pId}_customer-${customer}`, player.customers[customer]);
                    this.addCustomTooltip(`rog_reserve_${pId}_customer-${customer}`, this.fsr(_('Deliveries to ${customer}'),{customer:customerName}));
                });

                this.addCustomTooltip(`icon_point_${pId}`, _('Score'));
                this.addCustomTooltip(`rog_reserve_${pId}_money`, _('Koku'));
                this.addCustomTooltip(`rog_reserve_${pId}_rice`, _('Rice'));
                this.addCustomTooltip(`rog_reserve_${pId}_silk`, _('Silk'));
                this.addCustomTooltip(`rog_reserve_${pId}_pottery`, _('Porcelain'));
                this.addCustomTooltip(`rog_reserve_${pId}_favor`, _('Divine favor'));
                this.addCustomTooltip(`rog_reserve_${pId}_dieFace`, _('Die'));
                
                this.addCustomTooltip(`rog_reserve_${pId}_port`, this.BUILDING_TYPES[BUILDING_TYPE_PORT]);
                this.addCustomTooltip(`rog_reserve_${pId}_manor`, this.BUILDING_TYPES[BUILDING_TYPE_MANOR]);
                this.addCustomTooltip(`rog_reserve_${pId}_market`, this.BUILDING_TYPES[BUILDING_TYPE_MARKET]);
                this.addCustomTooltip(`rog_reserve_${pId}_shrine`, this.BUILDING_TYPES[BUILDING_TYPE_SHRINE]);

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
        
        refreshPlayerColor(pid,color) {
            debug("refreshPlayerColor",pid,color);
            //update player color :
            this.gamedatas.players[pid].color = color;
            this.gamedatas.players[pid].color_back = (color == "ffffff") ? "bbbbbb" : null;
            let divSidePanel = $(`overall_player_board_${pid}`);
            divSidePanel.dataset.color = color;
            let divName = divSidePanel.querySelector(`#player_name_${pid}`).querySelector(`a:first-child` );
            divName.style.color = ` #${color}`;
            divName.dataset.color = color;
            let divHand =  $(`rog_player_hand-${pid}`);
            if(divHand){
                //if current player
                divHand.dataset.color = color;
                divHand.querySelector(`.player-name`).style.color = `#${color}`;
                divHand.querySelector(`.player-name`).dataset.color = color;
            }
            let divDelivered =  $(`rog_player_delivered-${pid}`);
            divDelivered.dataset.color = color;
            divDelivered.querySelector(`.rog_title`).innerHTML = this.fsr(_('${player_name} delivered'), { player_name:this.coloredPlayerName(this.gamedatas.players[pid].name)});
            this.updateScoreMarkerColor(pid,color);
        },
        updatePlayerOrdering() {
            debug("updatePlayerOrdering");
            this.inherited(arguments);
            dojo.place('player_board_config', 'player_boards', 'first');
        },
        setupInfoPanel() {
            debug("setupInfoPanel");
                    
            dojo.place(this.tplConfigPlayerBoard(), 'player_boards', 'first');
            this._counters['era'] = this.createCounter('rog_counter_era',this.gamedatas.era);
            //this._counters['turn'] = this.createCounter('rog_counter_turn',this.gamedatas.turn);
            
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
            let era = this.gamedatas.era;
            let gameLastTurns = this.gamedatas.endTriggered;
            return `
            <div class='player-board' id="player_board_config">
                <div id="player_config" class="player_board_content">
                <div class="player_config_row" id="turn_counter_wrapper">
                    ${_('Era')} <span id='rog_counter_era'>${era}</span>
                    <span id='rog_end_warning' class='${ gameLastTurns ? "":"rog_nodisplay"}'><hr>${_('Last turns !')}</span>
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
                <div class='rog_player_resource_line rog_player_resource_line_money'>
                    ${this.tplResourceCounter(player, 'money',  NB_MAX_MONEY)}
                    ${this.tplResourceCounter(player, 'favor',  player.moon)}
                </div>
                <div class='rog_player_resource_line rog_player_resource_line_materials'>
                    <hr>
                    ${this.tplResourceCounter(player, 'silk',   NB_MAX_RESOURCE)}
                    ${this.tplResourceCounter(player, 'rice',   NB_MAX_RESOURCE)}
                    ${this.tplResourceCounter(player, 'pottery',NB_MAX_RESOURCE)}
                </div>
                <div class='rog_player_resource_line rog_player_resource_line_buildings'>
                    <hr>
                    ${this.tplResourceCounter(player, 'port')}
                    ${this.tplResourceCounter(player, 'manor')}
                    ${this.tplResourceCounter(player, 'market')}
                    ${this.tplResourceCounter(player, 'shrine')}
                </div>
                <div class='rog_player_resource_line rog_player_resource_line_i1'>
                    <hr>
                    <div class='rog_icon_influence'></div>
                    ${this.tplResourceCounter(player, 'influence-1')}
                    ${this.tplResourceCounter(player, 'influence-2')}
                    ${this.tplResourceCounter(player, 'influence-3')}
                </div>
                <div class='rog_player_resource_line rog_player_resource_line_i2'>
                    <div class='rog_icon_influence' style='visibility: hidden;'></div>
                    ${this.tplResourceCounter(player, 'influence-4')}
                    ${this.tplResourceCounter(player, 'influence-5')}
                    ${this.tplResourceCounter(player, 'influence-6')}
                </div>
                <div class='rog_player_resource_line rog_player_resource_line_customers'>
                    <hr>
                    ${this.tplResourceCounter(player, 'customer-1')}
                    ${this.tplResourceCounter(player, 'customer-2')}
                    ${this.tplResourceCounter(player, 'customer-3')}
                    ${this.tplResourceCounter(player, 'customer-4')}
                    ${this.tplResourceCounter(player, 'customer-5')}
                </div>
                <hr>
                <div class='rog_player_resource_line rog_player_resource_line_clan'>
                    <div id='rog_player_clan_panel-${player.id}' class='rog_player_clan_panel'>
                        ${player.clan ? this.formatIcon('clan-'+player.clan,this.CLANS_NAMES.get(player.clan)) :''}
                    </div>
                    ${this.tplResourceCounter(player, 'dieFace')}
                </div>
                <div class='rog_player_resource_line_clan_patron'
                     id='rog_player_patron-${player.id}'>
                </div>
            </div>
            </div>`;
        },
        /**
         * Use this tpl for any counters that represent qty of tokens
         */
        tplResourceCounter(player, res, totalValue = null) {
            let totalText = totalValue ==null ? '' : `<span id='rog_counter_${player.id}_${res}_total' class='rog_resource_${res}_total'>${totalValue}</span> `;
            return `
            <div class='rog_player_resource rog_resource_${res}'>
                <span id='rog_counter_${player.id}_${res}' 
                class='rog_resource_${res}'></span>${totalText}${this.formatIcon(res, null)}
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
    
            if (n >= 0) {
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
        
        gainPoints(pId,n, targetSource = null) {
            this.gamedatas.players[pId].score += n;
            this.moveScoreMarker(this.gamedatas.players[pId]);
            
            if (this.isFastMode()) {
                this.scoreCtrl[pId].incValue(n);
                return Promise.resolve();
            }
    
            let elem = `<div id='rog_score_animation'>
                ${Math.abs(n)}
                <div class="rog_icon_container rog_icon_container_score">
                    <div class="rog_icon rog_icon_score"></div>
                </div>
                </div>`;
            $('page-content').insertAdjacentHTML('beforeend', elem);
            
            if (n > 0) {
                return this.slide(`rog_score_animation`, `player_score_${pId}`, {
                    from: targetSource || this.getVisibleTitleContainer(),
                    destroy: true,
                    phantom: false,
                    duration: 800,
                }).then(() => this.scoreCtrl[pId].incValue(n));
            } else {
                this.scoreCtrl[pId].incValue(n);
                return this.slide(`rog_score_animation`, targetSource || this.getVisibleTitleContainer(), {
                    from: `player_score_${pId}`,
                    destroy: true,
                    phantom: false,
                    duration: 800,
                });
            }
        },
            
        updatePlayerDieFace(pId,dieFace, animate = false) {
            debug("updatePlayerDieFace",pId,dieFace,animate );
            let counter = this._counters[pId].dieFace;
            counter.toValue(dieFace);
            let icon = counter.span.nextSibling.firstElementChild;
            icon.dataset.face = dieFace;
            
            if(animate && !this.isFastMode()){
                let elem = `<div id='rog_dieFace_animation'>
                    ${dieFace}
                    <div class="rog_icon_container rog_icon_container_dieFace">
                        <div class="rog_icon rog_icon_die_face-${dieFace}"></div>
                    </div>
                    </div>`;
                $('page-content').insertAdjacentHTML('beforeend', elem);

                this.slide(`rog_dieFace_animation`, `rog_reserve_${pId}_dieFace`, {
                    from: this.getVisibleTitleContainer(),
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
        setupCards(keepHand = false) {
            debug("setupCards",keepHand);
            // This function is refreshUI compatible
            //destroy previous cards
            document.querySelectorAll('.rog_card[id^="rog_card-"], .rog_clan_card[id^="rog_clan_card-"]').forEach((oCard) => {
                if(keepHand && oCard.parentNode.parentNode.parentNode.classList.contains('rog_cards_hand')) return;
                this.destroy(oCard);
            });
            if(!keepHand){
                document.querySelectorAll('.rog_cards_hand').forEach((div) => {
                    this.empty(div);
                });
            }
            document.querySelectorAll('.rog_cards_delivered').forEach((div) => {
                this.empty(div);
            });
            let cardIds = this.gamedatas.cards.map((card) => {
                let divCardId = `rog_card-${card.id}`;
                if(card.subtype == CARD_TYPE_CLAN_PATRON ) divCardId = `rog_clan_card-${card.id}`;
                if (!$(divCardId)) {
                    this.addCard(card);
                }
        
                let o = $(divCardId);
                if (!o) return null;
        
                let container = this.getCardContainer(card);
                if (o.parentNode != $(container)) {
                    dojo.place(o, container);
                }
                return card.id;
            });
        },
    
        addCard(card, location = null) {
            debug('addCard',card);
            if(card.subtype == CARD_TYPE_CLAN_PATRON ) return this.addClanCard(card, location);
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
            let desc = this.getCustomerCardName(cardDatas);
            let div = this.tplCard(cardDatas,'_tmp');
            return [`<div class='rog_card_tooltip'><h1>${desc}</h1>${div}</div>`];
        },
        getCustomerCardName(cardDatas) {
            return [this.fsr(_('${customer_type} ${region}'), { customer_type: _(cardDatas.title), region: cardDatas.region })];
        },
        getCustomerDeliveryAbilityText(card) {
            let regionIcon =  this.formatIcon('influence-'+card.region);
            let customerIcon = this.formatIcon('customer-'+card.customerType);
            let descriptionMap = new Map([
                [CUSTOMER_TYPE_ARTISAN,     this.fsr(_('Gain ${influence_2} in ${region} and place a clan marker on ${artisan_space} there.'),{influence_2:this.formatIcon("influence",2), region: regionIcon,artisan_space:customerIcon })],
                [CUSTOMER_TYPE_ELDER,       this.fsr(_('Place a clan marker on ${elder_space} in ${region}.'), {region: regionIcon,elder_space:customerIcon })],
                [CUSTOMER_TYPE_MERCHANT,    this.fsr(_('Gain ${influence_3} in ${region}. Place a clan marker on ${merchant_space} at the end of the river.'),{influence_3:this.formatIcon("influence",3), region: regionIcon,merchant_space:customerIcon })],
                [CUSTOMER_TYPE_MONK,        this.fsr(_('${moon_up} and gain 2 ${icon_favor}.<br>Place a second clan marker on ${icon_building}.'),{moon_up:this.formatIcon("moon_up"), icon_favor:this.formatIcon(RESOURCES[RESOURCE_TYPE_SUN]), icon_building:this.formatIcon("bonus-"+ (card.monkType==MONK_TYPE_OWN_BUILDING ? BONUS_TYPE_SECOND_MARKER_ON_BUILDING : BONUS_TYPE_SECOND_MARKER_ON_OPPONENT )) })],
                [CUSTOMER_TYPE_NOBLE,       this.fsr(_('${ship_upgrade} and gain ${influence_2} in ${region}.'),{ship_upgrade:this.formatIcon("bonus-"+BONUS_TYPE_UPGRADE_SHIP),influence_2:this.formatIcon("influence",2), region: regionIcon,})],
            ]);
            return descriptionMap.get(card.customerType);
        },
        getCustomerOngoingAbilityText(card) {
            let ongoingAbility = null;
            let regionIcon =  this.formatIcon('influence-'+card.region);
            let moneyIcon =  this.formatIcon('money');
            switch(card.customerType){
                case CUSTOMER_TYPE_ARTISAN:   
                    ongoingAbility = this.fsr(_('Pay 2 ${money} less to build in ${region}.'), {money:moneyIcon, region:regionIcon});
                    break;
                case CUSTOMER_TYPE_ELDER:
                    ongoingAbility = null; 
                    break;
                case CUSTOMER_TYPE_MERCHANT:  
                    switch(card.type){
                        case CARD_MERCHANT_1:
                            ongoingAbility = this.fsr(_('When you complete your journey, gain ${influence_1} in every region with ${building}'), {influence_1:this.formatIcon("influence",1), building: this.formatIcon("bonus-"+BONUS_TYPE_SECOND_MARKER_ON_BUILDING) });
                            break;
                        case CARD_MERCHANT_2:
                            ongoingAbility = this.fsr(_('When you complete your journey, gain ${score} : ${delivery}.'), {score:this.formatIcon("score_1"),delivery:this.formatIcon("customer_delivery")});
                            break;
                        case CARD_MERCHANT_3:
                            ongoingAbility = this.fsr(_('When you complete your journey, gain ${n} ${icon}'), {n:'',icon:this.formatIcon("bonus-"+BONUS_TYPE_CHOICE)});
                            break;
                        case CARD_MERCHANT_4:
                            ongoingAbility = this.fsr(_('When you complete your journey, you may sell goods for ${n} ${money} each.'), {n:REGION_5, money:moneyIcon,});
                            break;
                        case CARD_MERCHANT_5:
                            ongoingAbility = this.fsr(_('When you complete your journey, gain ${n} ${icon}.'), {n:1,icon:this.formatIcon(RESOURCES[RESOURCE_TYPE_SUN]),});
                            break;
                        case CARD_MERCHANT_6:
                            ongoingAbility = this.fsr(_('When you complete your journey, gain +${n} ${money}'), {n:3, money:moneyIcon,});
                            break;
                    }
                    break;
                case CUSTOMER_TYPE_MONK: 
                    let icon_building ='';
                    if(card.monkType==MONK_TYPE_OWN_BUILDING ){
                        icon_building = this.formatIcon("bonus-"+BONUS_TYPE_SECOND_MARKER_ON_BUILDING);
                        ongoingAbility = this.fsr(_('You gain double owner rewards from ${building} with your extra clan marker.'), {building:icon_building});
                    } else {
                        icon_building = this.formatIcon("bonus-"+BONUS_TYPE_SECOND_MARKER_ON_OPPONENT);
                        ongoingAbility = this.fsr(_('You also gain the owner rewards from ${building} with your clan marker.'), {building:icon_building});
                    }
                    break;
                case CUSTOMER_TYPE_NOBLE:
                    switch(card.type){
                        case CARD_NOBLE_1:
                            ongoingAbility = this.fsr(_('When your ${ship_type} gains a good from visiting a ${market}, it may be ${resource}.'), {ship_type:MEEPLE_TYPE_SHIP_ROYAL,market:this.formatIcon("market_simple"),resource:this.formatIcon('bonus-'+BONUS_TYPE_CHOICE) });
                            break;
                        case CARD_NOBLE_2:
                            ongoingAbility = this.fsr(_('Your ${ship_type} collects +1 ${money} from each empty space it visits.'), { ship_type:MEEPLE_TYPE_SHIP_ROYAL,money:moneyIcon});
                            break;
                        case CARD_NOBLE_3:
                            ongoingAbility = this.fsr(_('After your ${ship_type} sails, gain ${influence_1} in each adjacent region.'), {ship_type:MEEPLE_TYPE_SHIP_ROYAL,influence_1:this.formatIcon("influence",1),  });
                            break;
                        case CARD_NOBLE_4:
                            ongoingAbility = this.fsr(_('After your ${ship_type} sails, gain ${score} if you visited at least 1 ${building}.'), {ship_type:MEEPLE_TYPE_SHIP_ROYAL,score:this.formatIcon("score_1"),building:this.formatIcon("bonus-"+BONUS_TYPE_SECOND_MARKER_ON_OPPONENT) });
                            break;
                        case CARD_NOBLE_5:
                            ongoingAbility = this.fsr(_('When your ${ship_type} sails, you may adjust your die up or down by 1 (for free).'), {ship_type:MEEPLE_TYPE_SHIP_ROYAL, });
                            break;
                        case CARD_NOBLE_6:
                            ongoingAbility = this.fsr(_('After your ${ship_type} sails, gain ${score} if you visited at least 1 ${building}.'), {ship_type:MEEPLE_TYPE_SHIP_ROYAL,score:this.formatIcon("score_1"),building:this.formatIcon("bonus-"+BONUS_TYPE_SECOND_MARKER_ON_BUILDING) });
                            break;
                    }
                    break;
            }
            ongoingAbility = (ongoingAbility ==null) ? '' :`<span class='rog_ongoing_ability'><div class='reduceToFit'>${ongoingAbility}</div></span>`;
            return ongoingAbility;
        },
        getCustomerEndgameAbilityText(card) {
            let endgameAbility = null;
            let regionIcon =  this.formatIcon('influence-'+card.region);
            switch(card.customerType){
                case CUSTOMER_TYPE_ARTISAN:   
                    endgameAbility = this.fsr(_('${score} : ${n} ${element}'), {score:this.formatIcon('score_1'),n:3, element: this.formatIcon('bonus-'+BONUS_TYPE_CHOICE)});
                    break;
                case CUSTOMER_TYPE_ELDER:     
                    endgameAbility = this.fsr(_('2x ${score} from ${region}'), {score:this.formatIcon('score'),region:regionIcon}); 
                    break;
                case CUSTOMER_TYPE_MERCHANT:  
                    endgameAbility = this.fsr(_('${score} : ${n} ${element}'), {score:this.formatIcon('score_1'),n:5, element: this.formatIcon('money')});
                    break;
                case CUSTOMER_TYPE_MONK:      
                    endgameAbility = null;
                    break;
                case CUSTOMER_TYPE_NOBLE:
                    let icon_score = this.formatIcon("score_1");
                    switch(card.type){
                        case CARD_NOBLE_1:
                            icon_building = this.formatIcon("market");
                            endgameAbility = this.fsr(_('${score} : ${n} ${element}'), {score:icon_score, n:'', element:icon_building});
                            break;
                        case CARD_NOBLE_2:
                            icon_building = this.formatIcon("port");
                            endgameAbility = this.fsr(_('${score} : ${n} ${element}'), {score:icon_score, n:'', element:icon_building});
                            break;
                        case CARD_NOBLE_3:
                            icon_building = this.formatIcon("manor");
                            endgameAbility = this.fsr(_('${score} : ${n} ${element}'), {score:icon_score, n:'', element:icon_building});
                            break;
                        case CARD_NOBLE_4:
                            icon_building = this.formatIcon("shrine");
                            endgameAbility = this.fsr(_('${score} : ${n} ${element}'), {score:icon_score, n:'', element:icon_building});
                            break;
                        case CARD_NOBLE_5:
                            icon_building = this.formatIcon("bonus-"+BONUS_TYPE_SECOND_MARKER_ON_BUILDING);
                            endgameAbility = this.fsr(_('${score} : type of ${building}'), {score:icon_score, building:icon_building});
                            break;
                        case CARD_NOBLE_6:
                            icon_building = this.formatIcon("bonus-"+BONUS_TYPE_SECOND_MARKER_ON_BUILDING);
                            endgameAbility = this.fsr(_('${score} : region with ${building}'), {score:icon_score, building:icon_building});
                            break;
                    }
                    break;
            }
            endgameAbility = (endgameAbility ==null) ?'' :`<span class='rog_endgame_ability'><div class='reduceToFit'>${endgameAbility}</div></span>`;
            return endgameAbility;
        },

        tplCard(card, prefix ='') {
            let customerName = this.getCustomerCardName(card);
            let delivAbility = this.getCustomerDeliveryAbilityText(card);
            let ongoingAbility = this.getCustomerOngoingAbilityText(card);
            let endgameAbility = this.getCustomerEndgameAbilityText(card);
            return `<div class="rog_card" id="rog_card${prefix}-${card.id}" data-id="${card.id}" data-type="${card.type}" data-customertype="${card.customerType}" data-customer_name="${customerName}">
                    <div class="rog_card_wrapper">
                        <span class='rog_customer_name'><div class='reduceToFit'>${_(card.title)}</div></span>
                        <span class='rog_delivery_ability'><div class='reduceToFit'>${delivAbility}</div></span>
                        ${ongoingAbility}
                        ${endgameAbility}
                    </div>
                </div>`;
        },
    
        getCardContainer(card) {
            if (card.location == CARD_LOCATION_HAND) {
                let holder = this.addCustomerCardHolder(card,`rog_cards_hand`);
                if( holder){
                    return holder.id;
                }
                return $(`rog_cards_hand-${card.pId}`);
            }
            if (card.location == CARD_LOCATION_DELIVERED) {
                let holder = this.addCustomerCardHolder(card,`rog_cards_delivered`);
                if( holder){
                    return holder.id;
                }
                return $(`rog_cards_delivered-${card.pId}`);
            }
            if (card.location == CARD_CLAN_LOCATION_ASSIGNED) {
                return $(`rog_player_patron-${card.pId}`);
            }
    
            console.error('Trying to get container of a card', card);
            return 'game_play_area';
        },
            
        tplPlayerHand(player) {
            return `<div class='rog_player_hand_resizable'>
                <div id='rog_player_hand-${player.id}' class='rog_player_hand' data-color='${player.color}'>
                    <div class='player-name' style='color:#${player.color}' data-color='${player.color}'>${_('My hand')}</div>
                    <div class='rog_cards_hand' id='rog_cards_hand-${player.id}'></div>
                </div>
            </div>`;
        },
        tplPlayerDeliveredCards(player) {
            return `<div class='rog_player_delivered_resizable' id='rog_player_delivered_resizable-${player.id}'>
                <div id='rog_player_delivered-${player.id}' class='rog_player_delivered' data-color='${player.color}'>
                    <h3 class='rog_title' >${this.fsr(_('${player_name} delivered'), { player_name:this.coloredPlayerName(player.name)}) }</h3>
                    <div class='rog_cards_delivered' id='rog_cards_delivered-${player.id}'></div>
                </div>
            </div>`;
        },
        /**
         * @param string location : 'rog_cards_hand' /'rog_cards_delivered'
         */
        addCustomerCardHolder(card,location) {
            debug("addCustomerCardHolder",card,location);
            let divId = `rog_customer_holder_${location}-${card.id}`;
            if ($(divId)) return $(divId);
            let elt = this.place('tplCustomerCardHolder', {card,location}, $(`${location}-${card.pId}`));
            return elt.firstElementChild;
        },
        tplCustomerCardHolder(datas) {
            return `<div class="rog_customer_card_resizeable" id="rog_customer_card_resizeable_${datas.location}-${datas.card.id}"  data-type='${datas.card.type}'>
                    <div class="rog_customer_holder" id="rog_customer_holder_${datas.location}-${datas.card.id}"></div>
                </div>`;
        },

        ////////////////////////////////////////////////////////
        // Clan cards
        ////////////////////////////////////////////////////////
        addClanCard(card, location = null) {
            debug('addClanCard',card);
            if ($('rog_clan_card-' + card.id)) return;
    
            let o = this.place('tplClanCard', card, location == null ? this.getCardContainer(card) : location);
            let tooltipDesc = this.getClanCardTooltip(card);
            if (tooltipDesc != null) {
                this.addCustomTooltip(o.id, tooltipDesc.map((t) => this.formatString(t)).join('<br/>'));
            }
            this.reduceTextSizeOnCardElements(o);
    
            return o;
        },
        tplClanCard(card, card_side = 0, prefix ='') {
            let descriptionMap = new Map([
                [PATRON_MASTER_ENGINEER, this.fsr(_('Start with +${n} ${icon_money}. You may build in any region ignoring your die.'),{n:10,icon_money:this.formatIcon(RESOURCES[RESOURCE_TYPE_MONEY])})],
                [PATRON_TRADER         , this.fsr(_('When you build, score ${icon_points} for each river space touching the building.'),{n:1,icon_points:this.formatIcon('score',1)})],
                [PATRON_SON_OF_STORM   , this.fsr(_('Start the game with ${n} ${icon_card} instead of 2. You may ignore a customer\'s required die face when delivering.'),{n:3,icon_card:this.formatIcon('customer_card')})],
                [PATRON_PRIESTESS      , this.fsr(_('When you deliver, score ${icon_points} if this is the first customer in the region you are delivering to.'),{n:3,icon_points:this.formatIcon('score',3)})],
                [PATRON_IRON_CRANE     , this.fsr(_('When you sail, gain ${icon_points} if you visit 1 or more ${icon_opponent}'),{n:2,icon_points:this.formatIcon('score',2),icon_opponent:this.formatIcon('opponent_building')})],
                [PATRON_DARLING        , this.fsr(_('You may spend ${n} ${icon_favor} before you roll your die to set it to any number'),{n:1,icon_favor:this.formatIcon(RESOURCES[RESOURCE_TYPE_SUN])})],
                [PATRON_GOVERNOR       , this.fsr(_('When another player passes your clan marker on an influence track score ${icon_points}. (You must have 1 or more influence on the track to use this power.)'),{n:3,icon_points:this.formatIcon('score',3)})],
                [PATRON_LADY           , this.fsr(_('When advancing on an influence track, you jump over other players\' markers. Collect all resources that you pass or reach.'),{ })],
            ]);
            let description = descriptionMap.get(card.type);
            let patron_name = _(card.name);
            return `<div class="rog_clan_card" id="rog_clan_card${prefix}-${card.id}" data-id="${card.id}" data-type="${card.type}" data-side="${card_side}">
                    <div class="rog_clan_card_wrapper">
                        <span class='rog_clan_name'><div class='reduceToFit'>${_(this.CLANS_NAMES.get(card.clan))}</div></span>
                        <span class='rog_patron_name'><div class='reduceToFit'>${patron_name}</div></span>
                        <span class='rog_patron_ability'><div class='reduceToFit'>${_(card.abilityName)}</div></span>
                        <span class='rog_patron_desc'><div class='reduceToFit'>${description}</div></span>
                    </div>
                </div>`;
        },
        
        getClanCardTooltip(card) {
            let cardDatas = card;
            let divFront = this.tplClanCard(cardDatas,0,'_tmp_front');
            let divBack = this.tplClanCard(cardDatas,1,'_tmp_back');
            return [`<div class='rog_card_tooltip'>
                        <div class='rog_card_front_back'>${divFront}${divBack}</div>
                </div>`];
        },

        ////////////////////////////////////////////////////////
        //  _____ _ _
        // |_   _(_) | ___  ___
        //   | | | | |/ _ \/ __|
        //   | | | | |  __/\__ \
        //   |_| |_|_|\___||___/
        //////////////////////////////////////////////////////////

        setupTiles() {
            debug("setupTiles");
            for(k=1;k<=NB_SHORE_SPACES;k++){
                if($(`rog_shore_space-${k}`)) continue;
                this.place(`tplShoreSpace`,k, $(`rog_shore_spaces`));
            }
            /*
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
            this.empty('rog_mastery_cards');

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
            this._counters['deckSize1'].toValue(this.gamedatas.deckSize.era1);
            this._counters['deckSize2'].toValue(this.gamedatas.deckSize.era2);
            
        },
        tplShoreSpace(position) {
            return `<div id='rog_shore_space-${position}' class='rog_shore_space' data-pos='${position}'></div>`;
        },
        
        addEmperorTile(shoreSpace) {
            debug('addEmperorTile',shoreSpace);
            let divId = `rog_emperor_tile`;
            if ($(divId)) return $(divId);
            let o = this.place('tplEmperorTile',shoreSpace,$(`rog_shore_space-${shoreSpace}`));
            return o;
        },
        tplEmperorTile(position) {
            return `<div id='rog_emperor_tile' class='rog_emperor_tile' data-pos='${position}'></div>`;
        },
    
        addTile(tile, location = null) {
            debug('addTile',tile);
            if(tile.subtype == TILE_TYPE_MASTERY_CARD ) return this.addMasteryCard(tile, location);
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
            let titleSize = 'h1';
            if(cardDatas.buildingType){
                return this.getBuildingTileTooltip(tile);
            }
            else if(cardDatas.subtype=TILE_TYPE_SCORING){
                return this.getScoringTileTooltip(tile);
            }
            let div = this.tplTile(cardDatas,'_tmp');
            return [`<div class='rog_tile_tooltip' data-subtype='${subtype}'><${titleSize}>${typeName}</${titleSize}>${div}</div>`];
        },
        getScoringTileTooltip(tile) {
            let typeName = this.fsr( _('Final scoring tile for influence in region ${n}'),{n:tile.pos} );
            let detail = '';
            let detailReminder = '';
            if(tile.maxSpaces){
                detailReminder = `<span class='rog_detail_reminder'>*</span>`;
                detail = `<div class='rog_detail'>${detailReminder}${this.fsr( _('2nd place awarded only if the 2nd-place player is within ${n} spaces of the 1st-place player'),{n: "<span class='rog_maxSpaces'>"+ tile.maxSpaces+"</span>"} )}</div>`;
            }
            let divImage = this.tplTile(tile,'_tmp');
            let titleLine1 = _('Clan position');
            let titleLine2 = _('Score');
            let line1 = '';
            let line2 = '';     
            let k = 0;
            Object.values(tile.scores).forEach((score) => {
                    k++;
                    line1 += `<td>${k}</td>`;
                    line2 += `<td>${score}${ (k==2) ? detailReminder :''}</td>`;
                });
            let table = `<table id="rog_finalscore_table">
                <tbody>
                    <tr>
                        <th>${titleLine1}</th>
                        ${line1}
                    </tr>
                    <tr>
                        <th>${titleLine2}</th>
                        ${line2}
                    </tr>
                </tbody>
                </table>`;
            return [`<div class='rog_tooltip rog_tile_tooltip' data-subtype='${tile.subtype}'>
                <h2>${typeName}</h2>
                ${divImage}
                ${table}
                ${detail}
            </div>`];
        },
        getBuildingTileTooltip(tile) {
            let cardDatas = tile;
            let typeName = this.BUILDING_TYPES[cardDatas.buildingType];
            let subtype = cardDatas.subtype;
            let titleSize = 'h1';
            let divImage = this.tplTile(cardDatas,'_tmp');
            let eraText = (cardDatas.era ==0) ? '': this.fsr( _('Era : ${n}'),{n:cardDatas.era} );
            let buildBonus = (cardDatas.bonus ==0) ? '': this.fsr( _('Build bonus : ${label}'),{label: this.formatReward(BONUS_TYPE_INFLUENCE, cardDatas.bonus)} );
            let ownerRewards = Object.values(cardDatas.ownerReward.entries).map((e) => "<li>"+this.formatReward(e.type,e.n)+"</li>").join('');
            let ownerRewardsText = (ownerRewards.length==0) ? '': this.fsr( _('Owner rewards : ${list}'),{list: ownerRewards} );
            let visitorRewards = Object.values(cardDatas.visitorReward.entries).map((e) => "<li>"+this.formatReward(e.type,e.n)+"</li>").join('');
            let visitorRewardsText = (visitorRewards.length==0) ? '': this.fsr( _('Visitor rewards : ${list}'),{list:visitorRewards} );
            return [`<div class='rog_tile_tooltip' data-subtype='${subtype}'>
                <${titleSize}>${typeName}</${titleSize}>
                <div class='rog_tile_era'>${eraText}</div>
                ${divImage}
                <div class='rog_building_tile_infos'>
                    <div class='rog_build_bonus'>${buildBonus}</div>
                    <div class='rog_owner_rewards'><ul>${ownerRewardsText}</ul></div>
                    <div class='rog_visitor_rewards'><ul>${visitorRewardsText}</ul></div>
                </div>
            </div>`];
        },
        tplTile(tile, prefix ='') {
            //let nbPlayers = tile.nbPlayers ? tile.nbPlayers[0] : '';
            let nbPlayers = tile.nbPlayers ? Object.values(this.gamedatas.players).length : '';
            return `<div class="rog_tile rog_tile${prefix}" id="rog_tile${prefix}-${tile.id}" data-id="${tile.id}" data-type="${tile.type}" data-subtype="${tile.subtype}"
                    data-nbPlayers="${nbPlayers}">
                </div>`;
        },
        ////////////////////////////////////////////////////////
        // Mastery cards
        ////////////////////////////////////////////////////////
        addMasteryCardHolder(tile) {
            debug("addMasteryCardHolder",tile);
            let divId = `rog_tile_holder-${tile.id}`;
            if ($(divId)) return $(divId);
            let elt = this.place('tplMasteryCardHolder', tile, $('rog_mastery_cards'));
            return elt.firstElementChild;
        },
        tplMasteryCardHolder(tile) {
            return `<div class="rog_mastery_cards_resizeable">
                <div class="rog_tile_holder" id="rog_tile_holder-${tile.id}"></div>
                </div>`;
        },
        addMasteryCard(tile, location = null) {
            debug('addMasteryCard',tile);
            let divId = `rog_tile-${tile.id}`;
            if ($(divId)) return $(divId);
            let o = this.place('tplMasteryCard', tile, location == null ? this.getTileContainer(tile) : location);
            this.addCustomTooltip(o.id, this.getMasteryCardTooltip(tile));
            this.reduceTextSizeOnCardElements(o);
            return o;
        },
        tplMasteryCard(tile, prefix ='') {
            let nbPlayers = tile.nbPlayers ? Object.values(this.gamedatas.players).length : '';
            let descriptionMap = new Map([
                [MASTERY_TYPE_AIR,      this.fsr(_('Deliver to 3 different customer types.'),{})],
                [MASTERY_TYPE_COURTS,   this.fsr(_('Reach the ${imperial_flower} in any region.'),{imperial_flower:this.formatIcon('imperial_flower')})],
                [MASTERY_TYPE_EARTH,    this.fsr(_('Own 3 buildings of the same type.'),{})],
                [MASTERY_TYPE_FIRE,     this.fsr(_('Deliver to 2 customers of the same type.'),{})],
                [MASTERY_TYPE_VOID,     this.fsr(_('Have at least ${influence_1} in all 6 regions.'),{influence_1:this.formatIcon("influence",1)})],
                [MASTERY_TYPE_WATER,    this.fsr(_('Own 1 building of each type.'),{})],
            ]);
            let description = descriptionMap.get(tile.scoringType);
            let customer_types = '';
            let building_types = '';
            if([MASTERY_TYPE_AIR,MASTERY_TYPE_FIRE].indexOf(tile.scoringType) >= 0){
                this.CUSTOMER_TYPES.forEach((value, key, map) =>{
                    let customer = key;
                    let customerName = value;
                    customer_types += `<div class="rog_customer_label" data-type="${customer}"><div class='reduceToFit'>${customerName}</div></div>`;
                });
            }
            else if([MASTERY_TYPE_EARTH,MASTERY_TYPE_WATER].indexOf(tile.scoringType) >= 0){
                this.BUILDING_TYPES.forEach((value, key, map) =>{
                    let type = key;
                    if(type==0) return;
                    let name = value;
                    building_types += `<div class="rog_building_label" data-type="${type}"><div class='reduceToFit'>${name}</div></div>`;
                });
            }
            return `<div class="rog_tile rog_tile${prefix} rog_masterycard" id="rog_tile${prefix}-${tile.id}" data-id="${tile.id}" data-type="${tile.type}" data-subtype="${tile.subtype}"
                    data-nbPlayers="${nbPlayers}" data-masteryType="${tile.scoringType}">
                    <div class="rog_masterycard_wrapper">
                        <span class='rog_mastery_title'><div class='reduceToFit'>${_(tile.title)}</div></span>
                        <span class='rog_masterydesc'><div class='reduceToFit'>${description}</div></span>
                        <span class='rog_mastery_customers'>${customer_types}</span>
                        <span class='rog_mastery_buildings'>${building_types}</span>
                    </div>
                </div>`;
        },
        getMasteryCardTooltip(tile) {
            let cardDatas = tile;
            let subtype = tile.subtype;
            let titleSize = 'h1';
            let divImage = this.tplMasteryCard(cardDatas,'_tmp');

            let title = _('Immediate score for claiming this goal');
            let titleLine1 = _('Clan position');
            let titleLine2 = _('Score');
            let line1 = `<td>1</td>
                         <td>2</td>
                         <td>3</td>`;
            let line2 = `<td>7</td>
                         <td>5</td>
                         <td>3</td>`;
            if(tile.nbPlayers == 2){
                line1 = `<td>1</td>`;
                line2 = `<td>5</td>`;
            }
            let table = `<table id="rog_score_mastery_table">
                <tbody>
                    <tr>
                        <th>${titleLine1}</th>
                        ${line1}
                    </tr>
                    <tr>
                        <th>${titleLine2}</th>
                        ${line2}
                    </tr>
                </tbody>
                </table>`;
            return `<div class='rog_tile_tooltip rog_masterycard_tooltip rog_tooltip' data-subtype='${subtype}'>
                    <${titleSize}>${title}</${titleSize}>
                    ${table}
                    ${divImage}
                </div>`;
        },
        
        ////////////////////////////////////////////////////////
    
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
        getEraTileTooltip() {
            let title = _('Building Board');
            return `<div class='rog_era_tile_tooltip'><h1>${title}</h1><div class='rog_era_tile_holder'></div></div>`;
        },

        ////////////////////////////////////////////////////////
        //  __  __                 _
        // |  \/  | ___  ___ _ __ | | ___  ___
        // | |\/| |/ _ \/ _ \ '_ \| |/ _ \/ __|
        // | |  | |  __/  __/ |_) | |  __/\__ \
        // |_|  |_|\___|\___| .__/|_|\___||___/
        //                  |_|
        //////////////////////////////////////////////////////////

        /**
         * This function is refreshUI compatible
         */
        setupMeeples() {
            debug("setupMeeples");
            this.addInfluenceTracks();
            for(k=1;k<=NB_RIVER_SPACES;k++){
                if($(`rog_river_space-${k}`)) continue;
                this.place(`tplRiverSpace`,k, $(`rog_river_spaces`));
            }
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

            this.addScoreTrackSpaces();
        },
    
        addMeeple(meeple, location = null) {
            debug('addMeeple',meeple);
            if ($('rog_meeple-' + meeple.id)) return $('rog_meeple-' + meeple.id);
    
            let o = this.place('tplMeeple', meeple, location == null ? this.getMeepleContainer(meeple) : location); 
            let tooltipDesc = this.getMeepleTooltip(meeple);
            if (tooltipDesc != null) {
                this.addCustomTooltip(o.id, tooltipDesc);
            }
            return o;
        },
        getMeepleTooltip(meeple) {
            if(meeple.type == MEEPLE_TYPE_SHIP_ROYAL){
                let desc = _('Royal Ship');
                return [`<div class='rog_meeple_tooltip'><h3>${desc}</h3></div>`];
            }
            return null;
        },
    
        tplMeeple(meeple, prefix ='') {
            const PERSONAL = [MEEPLE_TYPE_SHIP,MEEPLE_TYPE_CLAN_MARKER,MEEPLE_TYPE_SHIP_ROYAL,MEEPLE_TYPE_SCORE_MARKER];
            let color = PERSONAL.includes(meeple.type) ? ` data-color="${this.getPlayerColor(meeple.pId)}" data-pId="${meeple.pId}" ` : '';
            return `<div class="rog_meeple" id="rog_meeple${prefix}-${meeple.id}"
                 data-id="${meeple.id}" 
                 ${color}
                 data-type="${meeple.type}"
                 data-pos="${meeple.pos}">
                </div>`;
        },
    
        getMeepleContainer(meeple) {
            let locationParts = meeple.location.split('-');
            if (locationParts[0] == 'tile') {//MEEPLE_LOCATION_TILE
                // on tile
                $tileElt = $(`rog_tile-${locationParts[1]}`);
                return $tileElt;
            }
            if (locationParts[0] == 'i') {//MEEPLE_LOCATION_INFLUENCE
                // on influence track
                let region = locationParts[1];
                let position = meeple.pos;
                return $(`rog_influence_track_space_${region}_${position}`);
            }
            if (locationParts[0] == 'r') {//MEEPLE_LOCATION_RIVER
                //(boat) on river
                let position = meeple.pos;
                return $(`rog_river_space-${position}`);
            }
            if (locationParts[0] == 'artisan') {//MEEPLE_LOCATION_ARTISAN
                let region = locationParts[1];
                return $(`rog_artisan_space_${region}`);
            }
            if (locationParts[0] == 'elder') {//MEEPLE_LOCATION_ELDER
                let region = locationParts[1];
                return $(`rog_elder_space_${region}`);
            }
            if (locationParts[0] == 'merchant') {//MEEPLE_LOCATION_MERCHANT
                return $(`rog_merchant_space`);
            }
    
            console.error('Trying to get container of a meeple', meeple);
            return 'game_play_area';
        },
        
        tplRiverSpace(position) {
            return `<div id='rog_river_space-${position}' class='rog_river_space' data-pos='${position}'>
                        <div class="rog_river_shape" id="rog_river_shape-${position}"></div>
                    </div>`;
        },
        addScoreTrackSpaces(){
            for(let k=0; k<100;k++){
                if(!$(`rog_score_track_space_${k}`)) this.place(`tplScoreTrackSpace`,k, $(`rog_score_track`));
                /*For debugging, lets add a meeple in each : 
                this.forEachPlayer((player) => {
                    if(isDebug) this.addMeeple({id:`debug_score_${player.id}_${k}`,pId:player.id,type:MEEPLE_TYPE_SCORE_MARKER,pos:k},$(`rog_score_track_space_${k}`));
                });
                */
            }
            this.forEachPlayer((player) => {
                this.addScoreMarker(player);
            });
        },
        addScoreMarker(player){
            debug("addScoreMarker",player);
            let position = player.score % 100;
            let meeple = this.addMeeple({id:`score_${player.id}`,pId:player.id,type:MEEPLE_TYPE_SCORE_MARKER,pos:position},$(`rog_score_track_space_${position}`));
            meeple.classList.add("rog_score_meeple");
        },
        moveScoreMarker(player){
            debug("moveScoreMarker",player);
            let position = player.score % 100;
            let meeple_id = `score_${player.id}`;
            let currentPos = $(`rog_meeple-${meeple_id}`).parentNode;
            this.slide(`rog_meeple-${meeple_id}`, $(`rog_score_track_space_${position}`), {  
                from: currentPos,
                phantom: false,
            }).then( ()=> {
            });
        },
        updateScoreMarkerColor(pid,color){
            debug("updateScoreMarkerColor",pid,color);
            let meeple_id = `score_${pid}`;
            let meeple = $(`rog_meeple-${meeple_id}`);
            if(meeple) meeple.dataset.color = color;
        },
        addInfluenceTracks() {
            debug("addInfluenceTracks");
            Object.values(REGIONS).forEach((region) =>{
                let influence_track = `rog_influence_track_${region}`;
                if(!$(influence_track)) this.place(`tplInfluenceTrack`,region, $(`rog_influence_tracks`));
                for(k=0;k<=NB_MAX_INLFUENCE;k++){
                    let influence_track_space = `rog_influence_track_space_${region}_${k}`;
                    if(!$(influence_track_space)) this.place(`tplInfluenceTrackSpace`,{region:region, space:k}, $(influence_track));
                    this.empty(influence_track_space);
                }
                
                if(!$(`rog_artisan_space_${region}`)) this.place(`tplArtisanSpace`,region, $(`rog_artisan_spaces`));
                if(!$(`rog_elder_space_${region}`)) this.place(`tplElderSpace`,region, $(`rog_elder_spaces`));
                this.empty(`rog_artisan_space_${region}`);
                this.empty(`rog_elder_space_${region}`);
            });
        },
        tplInfluenceTrack(region) {
            return `<div class="rog_influence_track" id="rog_influence_track_${region}" data-region='${region}'></div>`;
        },
        tplInfluenceTrackSpace(datas) {
            return `<div class="rog_influence_track_space" id="rog_influence_track_space_${datas.region}_${datas.space}" data-pos='${datas.space}'></div>`;
        },
        tplScoreTrackSpace(space) {
            return `<div class="rog_score_track_space" id="rog_score_track_space_${space}" data-pos='${space}'></div>`;
        },
        tplArtisanSpace(region) {
            return `<div class="rog_artisan_space" id="rog_artisan_space_${region}" data-region='${region}'></div>`;
        },
        tplElderSpace(region) {
            return `<div class="rog_elder_space" id="rog_elder_space_${region}" data-region='${region}'></div>`;
        },


   });
});
//# sourceURL=riverofgold.js