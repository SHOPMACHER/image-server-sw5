// {namespace name=backend/media_manager/view/main}
// {block name="backend/media_manager/view/media/grid"}
// {$smarty.block.parent}
Ext.define('Shopware.apps.SmImageServer.view.media.Grid', {
    override: 'Shopware.apps.MediaManager.view.media.Grid',

    /**
     * Renders the preview column. If the entry is an image, the image will be rendered. Otherwise
     * the renderer renders an item (using a `div` box).
     *
     * @param { String } value - The value of the column
     * @param { Object } tdStyle - The style of the `td` element
     * @param { Shopware.apps.MediaManager.model.Media } record - The used record
     * @returns { String } Formatted output
     */
    previewRenderer: function (value, tdStyle, record) {
        var type = record.get('type').toLowerCase(),
            result;

        if (!record.data.created) {
            record.data.created = new Date();
        }

        switch (type) {
            case 'video':
                result = '<div class="sprite-blue-document-film" style="height:16px; width:16px;display:inline-block"></div>';
                break;

            case 'music':
                result = '<div class="sprite-blue-document-music" style="height:16px; width:16px;display:inline-block"></div>';
                break;

            case 'archive':
                result = '<div class="sprite-blue-document-zipper" style="height:16px; width:16px;display:inline-block"></div>';
                break;

            case 'pdf':
                result = '<div class="sprite-blue-document-pdf-text" style="height:16px; width:16px;display:inline-block"></div>';
                break;

            case 'vector':
                result = '<div class="sprite-blue-document-illustrator" style="height:16px; width:16px;display:inline-block"></div>';
                if (Ext.Array.contains(['svg'], record.data.extension)) {
                    // Fix styling for SVG images
                    var style = Ext.String.format('width:[0]px;max-height:[0]px', this.selectedPreviewSize);
                    if (record.get('height') > record.get('width')) {
                        style = Ext.String.format('max-width:[0]px;height:[0]px', this.selectedPreviewSize);
                    }
                    result = Ext.String.format('<div class="small-preview-image"><img src="[0]" style="[1]" alt="[2]" /></div>', value, style, record.get('name'));
                }
                break;

            case 'image':
                if (Ext.Array.contains(['tif', 'tiff'], record.data.extension)) {
                    result = '<div class="sprite-blue-document-image" style="height:16px; width:16px;display:inline-block"></div>';
                } else {
                    result = Ext.String.format('<div class="small-preview-image"><img src="[0]" style="max-width:[1]px;max-height:[1]px" alt="[2]" /></div>', value, this.selectedPreviewSize, record.get('name'));
                }
                break;

            default:
                result = '<div class="sprite-blue-document-text" style="height:16px; width:16px;display:inline-block"></div>';
                break;
        }

        return result;

    },
});
// {/block}
