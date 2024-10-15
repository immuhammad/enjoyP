define(function () {
     'use strict';
     var selectedRowsIndex = [];
     var selectedRows = [];
     var mixin = {

       /**
        * Defines if provided select/deselect actions is relevant.
        * E.g. there is no need in a 'select page' action if only one
        * page is available.
        *
        * @param {String} actionId - Id of the action to be checked.
        * @returns {Boolean}
        */
       isActionRelevant: function (actionId) {
           var pageIds         = this.getIds().length,
               multiplePages   = pageIds < this.totalRecords(),
               relevant        = true;

           switch (actionId) {
               case 'selectPage':
                   relevant = multiplePages && !this.isPageSelected(true);
                   break;

               case 'deselectPage':
                   relevant =  multiplePages && this.isPageSelected();
                   break;

               case 'selectAll':
                   relevant = !multiplePages && !this.allSelected();
                   break;

               case 'deselectAll':
                   relevant = !multiplePages && (this.totalSelected() > 0);
           }

           return relevant;
       },



       /**
        * Callback method to handle changes of selected items.
        *
        * @param {Array} selected - An array of currently selected items.
        */
         onSelectedChange: function (selected) {
            var self = this;

            selectedRowsIndex.forEach(function (val, index) {
                if (!selected.includes(val)) {
                   var res = document.getElementById('textshow').value;
                   var rep = selectedRows[index];
                   document.getElementById('textshow').value = res.replace(rep,'');
                   delete selectedRowsIndex[index];
                   delete selectedRows[index];
                }
            });

            self.selected().every(function (selectedId) {
                self.rows().every(function (row) {
                    if (row.entity_id == selectedId) {
                        if (!selectedRowsIndex.includes(row.entity_id)) {
                            selectedRows.push(row.type_id);
                            selectedRowsIndex.push(row.entity_id);
                            document.getElementById('textshow').value = document.getElementById('textshow').value + row.type_id + ' ';
                        } 

                        return false;
                    }

                    return true;
                });
                return true;
            });

            self.updateExcluded(selected)
                .countSelected()
                .updateState();
         }

     };

     return function (target) {
 // target == Result that Magento_Ui/.../columns returns.
         return target.extend(mixin); // new result that all other modules receive
     };
});
