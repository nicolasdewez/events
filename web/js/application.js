function ApplicationEdit(typeField, urlField)
{
    this.typeField = typeField;
    this.urlField= urlField;

    this.onChangeType = function(){
        if ('asynchronous' === this.typeField.val()) {
            this.urlField.removeAttr('required');
            return;
        }

        this.urlField.attr('required', 'required');
    };

    this.listenEvents = function() {
        this.typeField.change(this.onChangeType.bind(this));
    };

    this.listenEvents();
    this.onChangeType();    // In order to initialize
}

$(function() {
    new ApplicationEdit($('#application_eventsType'), $('#application_url'));
});
