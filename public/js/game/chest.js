var Chest = {
    update: function (color, artifactId) {
        if (typeof players[color].chest[artifactId] == undefined) {
            players[color].chest[artifactId] = {artifactId: artifactId, quantity: 1};
        } else {
            players[color].chest[artifactId].quantity++;
        }
    }
}