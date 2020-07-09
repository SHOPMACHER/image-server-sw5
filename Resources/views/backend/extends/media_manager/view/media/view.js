// s{namespace name=backend/media_manager/view/main}
// {block name="backend/media_manager/view/media/view"}
// {$smarty.block.parent}
Ext.define('Shopware.apps.ShopmacherImageServer5.view.media.View', {
    override: 'Shopware.apps.MediaManager.view.media.View',

    /**
     * Creates the template for the media view panel
     *
     * @return { object } generated Ext.XTemplate
     */
    createMediaViewTemplate: function () {
        console.warn("createMediaViewTemplate");
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
});
// {/block}
