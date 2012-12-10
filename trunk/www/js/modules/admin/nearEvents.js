$(document).ready(function(){
    var url = $('#hrefNearEvents').html();
    $('#date').change(function(){
        getNearEvents(url);
    });
    $('#timestart').change(function(){
        getNearEvents(url);
    });
    
    function getNearEvents(url){
        var date = $('#date').val();
        var time = $('#timestart').val();
        
        if(date != "" && time!=""){
            var datetime = date + " " + time;
            var source = url + '?datetime=' 
                + encodeURIComponent(datetime);
            
            $('#nearEvents').html('nacitam');
            $.get(source, function(data){
                $('#nearEvents').html(data);
            });
        }
    }
});