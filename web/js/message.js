function Messages(resendCollection, pagesCollection, form, buttonSearch, inputPage)
{
    this.resendCollection = resendCollection;
    this.pagesCollection = pagesCollection;
    this.form = form;
    this.buttonSearch = buttonSearch;
    this.inputPage = inputPage;

    this.onClickResendLink = function(e){
        e.preventDefault();
        var url = $(e.currentTarget).parent().attr('rel');
        $.post(url).always(function() {
            window.location.reload();
        });
    };

    this.onClickPage = function(e) {
        e.preventDefault();
        this.inputPage.val($(e.currentTarget).attr('rel'));
        this.form.submit();
    };

    this.onSubmitSearch = function(e) {
        this.inputPage.val(1);
    };

    this.listenEvents = function() {
        this.resendCollection.click(this.onClickResendLink.bind(this));
        this.pagesCollection.click(this.onClickPage.bind(this));
        this.buttonSearch.click(this.onSubmitSearch.bind(this));
    };

    this.listenEvents();
}

$(function() {
    new Messages($('.resend'), $('.pagination li a'), $('form[name=message_search]'), $('#message_search_submit'), $('#message_search_page'));
});

