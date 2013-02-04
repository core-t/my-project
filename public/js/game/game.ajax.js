function setProductionA(castleId) {
    var unitId
    var production = $('input:radio[name=production]:checked').val();
    if(production == 'stop'){
        unitId = -1;
    }else{
        unitId = getUnitId(production);
    }

    if(!unitId) {
        return;
    }
    if(castles[castleId].currentProduction == unitId){
        return;
    }
    $.getJSON('/production/set/castleId/'+castleId+'/unitId/'+unitId, function(result) {
        if(result.set) {
            if(unitId == -1){
                $('#castle'+castleId).html('');
            }else{
                $('#castle'+castleId).html($('<img>').attr('src','../img/game/castle_production.png').css('float','right'));
            }
            $('.message').remove();
            castles[castleId].currentProduction = unitId;
            castles[castleId].currentProductionTurn = 0;
        }
    });
}

function addTowerA(towerId){
    $.getJSON('/tower/add/tid/'+towerId+'/c/'+turn.color)
}

function webSocketOpenA(wssuid){
    $.getJSON('/websocket/open/wssuid/'+wssuid);
}