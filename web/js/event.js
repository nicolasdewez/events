function EventEdit(collection, idEntries, addLink, deleteLink)
{
    this.collection = collection;
    this.idEntries = idEntries;
    this.addLink = addLink;
    this.deleteLink = deleteLink;

    // Calculate index for new element in collection
    this.collection.data('index', this.collection.find('select').length);

    this.onClickAddLink = function(e){
        e.preventDefault();
        var prototype = this.collection.data('prototype');
        var index = this.collection.data('index');
        var newForm = prototype.replace(/__name__/g, index);

        this.collection.data('index', index + 1);

        var newFormLiWidget = $('<div class="col-md-6"></div>').append(newForm);
        var newDeleteLink = $('<a href="#"><span class="glyphicon glyphicon-trash"></span></a>');
        var newDeleteBlock = $('<div class="col-md-6"></div>').append(newDeleteLink);
        var newClearBlock = $('<div class="clear"></div>');
        var newFormElement = $('<div class="col-md-12"></div>').append(newFormLiWidget).append(newDeleteBlock).append(newClearBlock);

        this.collection.find('#' + this.idEntries).append(newFormElement);

        newDeleteLink.find('.glyphicon-trash').click(this.onClickDeleteLink.bind(this));
    };

    this.onClickDeleteLink = function(e) {
        e.preventDefault();
        var element = $(e.currentTarget);
        element.parent().parent().parent().remove();

        return false;
    };

    this.listenEvents = function() {
        this.addLink.click(this.onClickAddLink.bind(this));
        this.deleteLink.click(this.onClickDeleteLink.bind(this));
    };

    this.listenEvents();
}

$(function() {
    new EventEdit($('.collection'), 'event_applications', $('.glyphicon-plus'), $('.glyphicon-trash'));
});
