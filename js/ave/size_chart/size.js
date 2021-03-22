if (Ave == undefined) {
    var Ave = {};
}

Ave.Size = function () {
    this.initialize.apply(this, arguments);
};

Ave.Size.prototype =
{
    _dimensions: null,
    _sizes: null,
    _sizeRows: null,
    _table: {},
    _backgroundColorDimension: "#ffdcdb",
    _backgroundColorRegion: "#acdbed",
    initialize: function (options) {
        this._dimensions = options['dimensions'];
        this._sizes = options['sizes'];
        this._sizeRows = options['maxSizeAmount'];
        this.createTable();
    },
    createBtn: function () {
        var btn = document.createElement("a");
        btn.href = 'javascript:void(0);';
        btn.addClassName('form-button');
        return btn;
    },
    addPlusButton: function (cell) {
        var btn = this.createBtn();
        btn.innerHTML = "+";
        btn.onclick = function (e) {
            if (this.addRow(e.target.parentNode.parentNode.rowIndex + 1, 1)) {
                // e.target.parentElement.removeChild(e.target);
                var inputs = document.getElementById("chart_info_tabs_info_content").getElementsByTagName('input');
                var positions = [];
                var newRowIndex = e.target.parentNode.parentNode.rowIndex + 1;
                var newCell = this._table.rows[newRowIndex].insertCell(-1);
                var isFirst = 0;
                this.addDeleteButton(newCell);
                this.addPlusButton(newCell);
                for(var i = 0; inputs.length > i; i ++) {
                    if (inputs[i].name == 'position[]') {
                        positions.push(inputs[i]);
                        if (parseInt(inputs[i].value) >= parseInt(newRowIndex - 1)) {
                            if (isFirst === 1) {
                                inputs[i].value = parseInt(inputs[i].value) + 1;
                            }
                            isFirst = 1;
                        }
                    }
                }
            }
        }.bind(this);
        cell.appendChild(btn);
    },
    addDeleteButton: function (cell) {
        var btn = this.createBtn();
        btn.innerHTML = "-";
        btn.onclick = function (e) {
            this.deleteColumn(e.target.parentNode.parentNode.rowIndex);
        }.bind(this);
        cell.appendChild(btn);
    },
    addControlColumn: function () {
        var newCell;
        for (var i = 0; i < this._table.rows.length; i++) {
            newCell = this._table.rows[i].insertCell(-1);
            if (i == this._table.rows.length - 1) {
                this.addDeleteButton(newCell);
                this.addPlusButton(newCell);
            } else if (i == 0) {
                newCell.innerHTML = '';
            } else {
                this.addDeleteButton(newCell);
                this.addPlusButton(newCell);
            }
        }
    },
    addLegend: function () {
        var tr = this._table.insertRow(0), dimensionId, j = 0, td, text;
        tr.style.fontWeight = "bold";
        for (dimensionId in this._dimensions) {
            td = tr.insertCell(j++);
            if (this._dimensions.hasOwnProperty(dimensionId)) {
                text = this._dimensions[dimensionId]['name'];
                if ('dimension' == this._dimensions[dimensionId]['type']) {
                    td.style.backgroundColor = this._backgroundColorDimension;
                } else if ('region' == this._dimensions[dimensionId]['type']) {
                    td.style.backgroundColor = this._backgroundColorRegion;
                }
            } else {
                text = '';
            }

            td.innerHTML = text;
        }
        td = tr.insertCell(j++);
        td.style.backgroundColor = 'lightGreen';
        td.innerHTML = 'Position';
    },
    getRealDimensionId: function (dimensionId) {
        var dimension = dimensionId.split('_');
        return dimension['1'];
    },
    addRow: function (i, isNewRow) {
        var j = 0, tr, td, dimensionId, realDimensionId, region, text, input;
        if (i != 0 && (!i || i == undefined)) {
            i = -1;
        }

        tr = this._table.insertRow(i);
        for (dimensionId in this._dimensions) {
            text = '';
            if (this._dimensions.hasOwnProperty(dimensionId)
                && (realDimensionId = this.getRealDimensionId(dimensionId))
                && (region = this._sizes[realDimensionId]))
            {
                if (region['sizes'][i] && (isNewRow !== 1)) {
                    text = region['sizes'][i]['name'];
                }
            }

            input = document.createElement("input");
            input.type = "text";
            input.name = "size[" + realDimensionId + "][]";
            input.value = text;
            td = tr.insertCell(j++);
            td.appendChild(input);
        }

        input = document.createElement("input");
        input.type = "text";
        input.name = "position[]";
        input.value = (i !== -1) ? i : 99;
        if (isNewRow === 1) {
            input.value = i - 1;
        }
        td = tr.insertCell(j++);
        td.appendChild(input);

        if (i == -1) {
            td = tr.insertCell(j);
            this.addPlusButton(td);
        }

        return true;
    },
    createHeader: function () {
        var h = document.createElement("h3");
        h.innerText = 'Table of sizes:';
        return h;
    },
    createNote: function () {
        var p = document.createElement("p");
        p.innerHTML = 'There are two types of columns: dimensions and regions.<br>' +
            'Column with type Dimension must enter only as float value. For example: 10, 20.4 or 12.2-23.9. Please input all sizes in cm.<br>' +
            'Column with type Region can enter a string value. For example: X, XXL or 20-24.<br>' +
            'All Dimension fields will be used for calculating user sizes on user end.<br>' +
            '<br><strong>Legend:</strong><br>' +
            '<div style="display: inline-block; width: 10px; height: 10px; background-color: ' + this._backgroundColorDimension + ';"></div> - Dimension column <br>' +
            '<div style="display: inline-block; width: 10px; height: 10px; background-color: ' + this._backgroundColorRegion + ';"></div> - Region column';
        return p;
    },
    addRows: function () {
        for (var i = 0; i <= this._sizeRows; i++) {
            this.addRow(i);
        }
    },
    deleteColumn: function (rowId) {
        this._table.deleteRow(rowId);
    },
    createTable: function () {
        if (this._dimensions && Object.keys(this._dimensions).length > 0) {
            var tableBody = document.createElement('tbody'),
                holder = document.createElement('div');
            this._table = document.createElement('table');
            this._table.cellSpacing = 0;
            this._table.cellPadding = 0;
            this._table.appendChild(tableBody);
            holder.appendChild(this.createHeader());
            holder.addClassName('sizeGrid');
            this.addRows();
            this.addLegend();
            this.addControlColumn();
            holder.appendChild(this._table);
            holder.appendChild(this.createNote());
            document.getElementById("info").appendChild(holder);
        }
    }
};
