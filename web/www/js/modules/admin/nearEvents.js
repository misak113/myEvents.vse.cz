$(document).ready(function(){
    var url = baseUrl + '/admin/event/nearevents';
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
            
            $('#nearEvents').html();
            $.get(source, function(data){
                $('#nearEvents').html(data);
            });
        }
    }
});