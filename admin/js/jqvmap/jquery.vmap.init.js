jQuery(document).ready(function(){

    jQuery('#vmap').vectorMap({
        map: 'world_en',
        backgroundColor: null,
        color: '#ffffff',
        hoverOpacity: 0.7,
        selectedColor: '#666666',
        enableZoom: true,
        showTooltip: true,
        values: sample_data,
        scaleColors: ['#C8EEFF', '#006491'],
        normalizeFunction: 'polynomial',
        onLabelShow: function(event, label, code)
        {
            var count = sample_data[code];
            if(typeof sample_data[code] == 'undefined'){
                count =  0;
            }
            var message =  count + ' visits';
                
            label.text(code.toUpperCase() + ' ' + message);
            //alert(message);
        }
    });

 
});