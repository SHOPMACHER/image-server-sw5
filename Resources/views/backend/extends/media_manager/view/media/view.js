//
//{namespace name=backend/media_manager/view/main}
//{block name="backend/media_manager/view/media/view"}
//{$smarty.block.parent}
Ext.override(Shopware.apps.MediaManager.view.media.View, {
    createMediaViewTemplate: function () {
        var me = this,
            tSize = me.thumbnailSize,
            tStyle = Ext.String.format('style="width:[0]px;height:[0]px;"', tSize),
            imgStyle = Ext.String.format('style="max-width:[0]px;max-height:[0]px"', tSize - 2);

        return new Ext.XTemplate(
            '{literal}<tpl for=".">',
            Ext.String.format('<div class="thumb-wrap" id="{name}" [0]>', tStyle),
            // If the type is image, then show the image
            '<tpl if="this.isImage(type, extension)">',
            Ext.String.format('<div class="thumb" [0]>', tStyle),
            Ext.String.format('<div class="inner-thumb" [0]>', tStyle),
            Ext.String.format('<img src="{thumbnail}" title="{name}" [0] /></div>', imgStyle),
            '</div>',
            '</tpl>',

            // All other types should render an icon
            '<tpl if="!this.isImage(type, extension)">',
            Ext.String.format('<div class="thumb icon" [0]>', tStyle),
            '<div class="icon-{[values.type.toLowerCase()]}">&nbsp;</div>',
            '</div>',
            '</tpl>',
            '<span class="x-editable">{[Ext.util.Format.ellipsis(values.name, 9)]}.{extension}</span></div>',
            '</tpl>',
            '<div class="x-clear"></div>{/literal}',
            {
                /**
                 * Member function of the template to check if a certain file is an image.
                 *
                 * @param { string }type
                 * @param { string } extension
                 * @returns { boolean }
                 */
                isImage: function (type, extension) {
                    return me._isImage(type, extension);
                }
            }
        )
    },
    createInfoPanelTemplate: function () {
        var me = this;

        return new Ext.XTemplate(
            '{literal}<tpl for=".">',
            '<div class="media-info-pnl">',

            // If the type is image, then show the image
            '<tpl if="this.isImage(type, extension)">',
            '<div class="thumb">',
            '<div class="inner-thumb"><img src="{thumbnail}" title="{name}" /></div>',
            '</div>',
            '</tpl>',

            // All other types should render an icon
            '<tpl if="!this.isImage(type, extension)">',
            '<div class="thumb icon">',
            '<div class="icon-{[values.type.toLowerCase()]}">&nbsp;</div>',
            '</div>',
            '</tpl>',
            '<div class="base-info">',
            '<p>',
            '<strong>Download:</strong>',
            '<a class="link" target="_blank" href="{/literal}{url controller=MediaManager action=download}{literal}?mediaId={id}" title="{name}">{name}</a>',
            '</p>',
            '<p>',
            '<strong>' + me.snippets.mediaInfo.name + '</strong>',
            '<input type="text" disabled="disabled" value="{name}" />',
            '</p>',
            '<p>',
            '<strong>' + me.snippets.mediaInfo.uploadedon + '</strong>',
            '<span>{[this.formatDate(values.created)]}</span>',
            '</p>',
            '<p>',
            '<strong>' + me.snippets.mediaInfo.type + '</strong>',
            '<span>{[this.formatDataType(values.type, values.extension)]}</span>',
            '</p>',
            '<tpl if="width">',
            '<p>',
            '<strong>' + me.snippets.mediaInfo.resolution + '</strong>',
            '<span>{width} x {height} Pixel</span>',
            '</p>',
            '</tpl>',

            '<tpl>',
            '<p>',
            '<strong>' + me.snippets.mediaInfo.adress + '</strong>',
            '<a class="link" target="_blank" href="{path}" title="{name}">' + me.snippets.mediaInfo.mediaLink + '</a>',
            '</p>',
            '</tpl>',

            '<tpl if="thumbnails">',
            '<p>',
            '<strong>' + me.snippets.mediaInfo.thumbnails + '</strong>',
            '{[this.getThumbnailSizes(values.thumbnails)]}',
            '</p>',
            '</tpl>',
            '</div>',
            '</div>',
            '</tpl>{/literal}',
            {
                /**
                 * Renders a list of links to the thumbnails
                 *
                 * @param { Object } thumbs
                 * @returns { string }
                 */
                getThumbnailSizes: function (thumbs) {
                    var str = '';
                    var sizes = [];

                    // We extract a sort value from the size to be able to sort the list of thumbs
                    Ext.Object.each(thumbs, function (key, val) {
                        sizes.push({
                            'sort': parseInt(key.split('x')[0]),
                            'name': key,
                            'link': val
                        });
                    });

                    // Sorting the list of thumbnails to make it more pleasant to look at
                    sizes.sort(function (a, b) {
                        return a.sort > b.sort;
                    });

                    // Rendering each link
                    Ext.Object.each(sizes, function (i, element) {
                        str += Ext.String.format('<a href="[0]" class="link" target="_blank">[1]</a><br>',
                            element.link,
                            element.name
                        );
                    });

                    return str;
                },

                /**
                 * Member function of the template to check if a certain file is an image
                 *
                 * @param { string } type
                 * @param { string } extension
                 * @returns { boolean }
                 */
                isImage: function (type, extension) {
                    return me._isImage(type, extension);
                },

                /**
                 * Member function of the template which formats a date string
                 *
                 * @param { string } value - Date string in the following format: Y-m-d H:i:s
                 * @return { string } formatted date string
                 */
                formatDate: function (value) {
                    return Ext.util.Format.date(value);
                },

                /**
                 * Formats the output type
                 *
                 * @param { string } type - Type of the media
                 * @param { string } extension - File extension of the media
                 */
                formatDataType: function (type, extension) {
                    var result = '';

                    extension = extension.toUpperCase();
                    switch (type) {
                        case 'VIDEO':
                            result = extension + me.snippets.formatTypes.video;
                            break;
                        case 'MUSIC':
                            result = extension + me.snippets.formatTypes.music;
                            break;
                        case 'ARCHIVE':
                            result = extension + me.snippets.formatTypes.archive;
                            break;
                        case 'PDF':
                            result = me.snippets.formatTypes.pdf;
                            break;
                        case 'IMAGE':
                            result = extension + me.snippets.formatTypes.graphic;
                            break;
                        case 'VECTOR':
                            result = extension + me.snippets.formatTypes.vector;
                            break;
                        default:
                            result = me.snippets.formatTypes.unknown;
                            break;
                    }
                    return result;
                }
            }
        )
    },
});

//{/block}
