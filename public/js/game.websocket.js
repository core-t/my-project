function wsCastleOwner(castleId, color) {
    lWSC.channelPublish(channel,'c.'+castleId+'.'+color);
}

function wsTurn() {
    lWSC.channelPublish(channel,'t');
}

function wsPlayerArmies(color){
    lWSC.channelPublish(channel,'s.'+color);
}

function wsArmyMove(x, y, armyId) {
    lWSC.channelPublish(channel,'m.'+x+'.'+y+'.'+armyId);
}

function wsArmyAdd(armyId) {
    lWSC.channelPublish(channel,'a.'+armyId);
}
