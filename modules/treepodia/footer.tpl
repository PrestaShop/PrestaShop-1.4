<script type="text/javascript">
<!-- 
    document.write(unescape("%3Cscript src='" + ((document.location.protocol == 'https:') ? 'https://' : 'http://') + "api.treepodia.com/video/Treepodia.js' type='text/javascript'%3E%3C/script%3E"));
// -->
</script>
<script type="text/javascript"> 
{literal}
<!-- 
    document.write(unescape("%3Cscript src='" + ((document.location.protocol == 'https:') ? 'https://' : 'http://') + "api.treepodia.com/utils/ps-video-dialog.js?account={/literal}{$account_id}{literal}' type='text/javascript'%3E%3C/script%3E")); 
// -->
{/literal}
</script> 

<script type="text/javascript">
{literal}
<!-- 
    var video;
    function initTreepodia() {
        Treepodia.getProduct('{/literal}{$account_id}{literal}', '{/literal}{$product_sku}{literal}').requestVideo(handleVideo); 
    }
    function handleVideo(vid) {
        video = vid;
        if(vid.hasVideos()) {
            document.getElementById('trpd-img-btn').style.display = 'inline';
        }
    }
// -->
{/literal}
</script>