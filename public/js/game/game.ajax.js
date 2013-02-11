function setProductionA(castleId) {
    console.log(castleId);

    var unitId
    var production = $('input:radio[name=production]:checked').val();

    console.log(production);

    if(production == 'stop'){
        unitId = -1;
    }else{
        unitId = getUnitId(production);
    }

    if(!unitId) {
        console.log('Brak unitId!');
        return;
    }
    if(castles[castleId].currentProduction == unitId){
        console.log('Current production');
        return;
    }
    $.getJSON('/production/set/castleId/'+castleId+'/unitId/'+unitId, function(result) {
        if(result.set) {
            if(unitId == -1){
                $('#castle'+castleId).html('');
            }else{
                $('#castle'+castleId).html($('<img>').attr('src','../img/game/castle_production.png').css('float','right'));
            }
            removeM();
            castles[castleId].currentProduction = unitId;
            castles[castleId].currentProductionTurn = 0;
        }
    });
}

function addTowerA(towerId){
    $.getJSON('/tower/add/tid/'+towerId+'/c/'+turn.color)
}
