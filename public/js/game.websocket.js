
function wsCastleOwner(castleId, color) {
//     lWSC.broadcastGamingEvent({
//         'event':'castle',
//         'channel':channel,
//         'data':{castleId:castleId,color:color}
//     });
    lWSC.channelPublish(channel,'c.'+castleId+'.'+color);
}

function wsTurn() {
    lWSC.channelPublish(channel,'t');
}

function wsPlayerArmies(color){
//     lWSC.broadcastGamingEvent({
//         'event':'armies',
//         'channel':channel,
//         'data':{color:color}
//     });
    lWSC.channelPublish(channel,'s.'+color);
}

function wsArmyMove(x, y, armyId) {
//     lWSC.broadcastGamingEvent({
//         'event':'move',
//         'channel':channel,
//         'data':{'x':x,'y':y,'armyId':armyId}
//     });
    lWSC.channelPublish(channel,'m.'+x+'.'+y+'.'+armyId);
}

function wsArmyAdd(armyId) {
//     lWSC.broadcastGamingEvent({
//         'event':'add',
//         'channel':channel,
//         'data':{'armyId':armyId}
//     });
    lWSC.channelPublish(channel,'a.'+armyId);
}

// function wsArmyDelete(armyId, color) {
//     lWSC.broadcastGamingEvent({
//         'event':'delete',
//         'channel':channel,
//         'data':{'armyId':armyId,'color':color}
//     });
// }
