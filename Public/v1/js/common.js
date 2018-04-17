/**
 * Created by sdusz-01 on 18/2/26.
 */
(function () {
    var parms = (window.location.search).split('&');
    var tab=parms[0].split('=');
    console.log(tab[0])
    if (tab[0] == '?tab') {
        $(".changeTab li").removeClass('active');
        $(".changeTab li").eq(tab[1]-1).addClass('active');
        $(".changeContent>div").hide();
        $(".changeContent>div").eq(tab[1]-1).show();
    }
    $(".changeTab li").click(function () {
        $(".changeTab li").removeClass('active');
        $(this).addClass('active');
        $(".changeContent").children().hide();
        $(".changeContent").children().eq(index = $(this).index()).show();
    });
    $(".comchange").click(function () {
        $(this).next().toggle();
    });
})();