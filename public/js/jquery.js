$(function () {

    $(window).click(function() {
        $('li.dropdown').each(function () {
            $(this).removeClass("active");
        })
    });

    $('li.dropdown').click(function (event) {
        event.stopPropagation();
        $(this).addClass("active");
    });

    // formInputs();

});



$(document).ready(function(){
    $('input[type=file]').click(function(){
        alert('test');
    });
});

function formInputs(){

    $(document).on('focus', 'input, textarea, select', function () {
        $(this).parent().addClass("filled");
    });

    $(document).on('blur', 'input, textarea, select', function () {
        //alert(">" + $(this).val() + "<\n>" + $(this).innerHTML + "< " + ($(this).innerHTML ? "true" : "false"));
        if(!$(this).val() && !$(this).innerHTML) {
            $(this).parent().removeClass("filled");
        }
    });
}

function formInputsInit(){
    $('input, textarea, select').each(function () {
        if($(this).val() || $(this).innerHTML) {
            $(this).parent().addClass("filled");
        }
    })
}
