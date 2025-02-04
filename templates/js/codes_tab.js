$(document).ready(function () {
    let prevent_2_click = false;

    $(document).on('click', '[modal-opener]', function () {
        if (prevent_2_click) {
            return;
        }

        prevent_2_click = true;

        const modal_opener = $(this).attr('modal-opener');
        const $modal = $('[' + modal_opener + ']');

        processModalOpen(modal_opener, $(this), $modal);

        const il_signal = $modal.attr('modal-signal');

        $(this).trigger(il_signal,
            {
                'id' : il_signal, 'event' : 'click',
                'triggerer' : $(this),
                'options' : JSON.parse('[]')
            }
        );

        setTimeout(function () {
            prevent_2_click = false;
        }, 500);
    });

    function processModalOpen(modal_opener, $trigger, $modal) {
        switch (modal_opener) {
            case 'change_votes_modal':
                $modal.find('[surname="code"]').attr("value", $trigger.attr("livevoting-code"));
                $modal.find('[surname="votes"]').attr("value", $trigger.attr("livevoting-votes"));
                $modal.find('[surname="user"]').attr("value", $trigger.attr("livevoting-user"));
                break;
        }
    }

    setTimeout(function () {
        const $input = $('[surname="user"]');

        const searchForUsers = function(url, search){
            const call = m => o => o[m]();
            return fetch(url + '&q=' + search).then(call('json')).then(function(response){
                console.log(Object.values(response.items));
                return Object.values(response.items);
            });
        };

        $input.autocomplete({
            source: function (request, response) {
                searchForUsers($input.attr("autocomplete_url"), request.term)
                .then(r => response(r))
                .catch(error => {
                    console.error("Search error:", error);
                    response([]);
                });
            },
        });
    }, 500);
});