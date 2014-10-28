/**
 * Created by scastro on 19/10/14.
 */
define([], function(){
    function FiltersForm(params) {
        // Data: value is either null, 'like', or 'dislike'
        this.chosenValue = params.value;

        // Behaviors
        this.like = function () {
            this.chosenValue('like');
        }.bind(this);
        this.dislike = function () {
            this.chosenValue('dislike');
        }.bind(this);
    }

    return FiltersForm;
});
