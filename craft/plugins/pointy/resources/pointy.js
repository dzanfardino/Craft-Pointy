var setAssetIdField = function (assetId) {
    $('.js-assetId-input').val(assetId);
};

var setBgImage = function (assetUrl) {
    return $('img.pointy-image').attr('src', assetUrl);
};

var positionMarker = function () {
    var $marker = $('.js-marker');
    var xPos = $('.pointy-x').val();
    var yPos = $('.pointy-y').val();
    $marker.css({
        left: (xPos - 10) + 'px', top: (yPos - 10) + 'px'
    });
};

$(document).ready(function () {

    var marker_offset_x = 10;
    var marker_offset_y = 10;

    positionMarker();

    $('.js-pointy-coord').keyup(function(e) {
        positionMarker();
    });

    $('.js-add-image').on('click', function (e) {
        var element = e.currentTarget;
        var uploadLocation = ["folder:" + $(this).data('assetSource')];
        if (typeof this.assetSelectionModal == 'undefined') {
            this.assetSelectionModal = Craft.createElementSelectorModal('Asset', {
                resizable: false,
                sources: uploadLocation,
                multiSelect: false,
                criteria: {locale: this.elementLocale, kind: 'image'},
                onCancel: function () {
                },
                onSelect: function (asset) {
                    var assetUrl = asset[0].url;
                    setAssetIdField(asset[0].id);
                    setBgImage(assetUrl);
                }
            });
        } else {
            this.assetSelectionModal.show();
        }
    })

    $("img.pointy-image").click(function (e) {

        var field = $(this).closest('div.field');

        var elOffsetX = $(this).offset().left,
            elOffsetY = $(this).offset().top,
            x = Math.round(e.pageX - elOffsetX),
            y = Math.round(e.pageY - elOffsetY);

        field.find(".pointy-x").val(x);
        field.find(".pointy-y").val(y);

        if (x != 0 && y != 0) {
            field.find(".js-marker").css({
                left: (x - marker_offset_x) + 'px', top: (y - marker_offset_y) + 'px'
            });
        }

    });

        if ($('.js-magnify').length > 0) {
        $('.js-magnify').blowup({
            scale : 2
        })
    }
});

