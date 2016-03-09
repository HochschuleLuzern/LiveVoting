/**
 * Class xlvoCorrectOrder
 * @type {{}}
 */
var xlvoCorrectOrder = {
    init: function (json) {
        var config = JSON.parse(json);
        var replacer = new RegExp('amp;', 'g');
        config.base_url = config.base_url.replace(replacer, '');
        this.config = config;
        this.ready = true;
    },
    config: {},
    base_url: '',
    run: function () {
        this.addSortable();
    }
    ,
    addSortable: function () {
        $('#lvo_bar_movable').sortable({
            placeholder: "list-group-item list-group-item-default xlvolist-group-fix"
        });
        $("#lvo_bar_movable").disableSelection();
    }
};
