if (Ave == undefined) {
    var Ave = {};
}

var ave_sizechart;
Ave.SizeChart = function () {
    this.initialize.apply(this, arguments);
};

Ave.SizeChart.prototype =
{
    inputDimensionClass: 'ave-sizechart-input-dimension',
    inputSelectDimensionName: 'ave_sizechart_dimension_select',
    dimensions: [],
    dimensionList: [],
    members: [],
    membersMeasurements: [],
    sizes: [],
    currentDimension: '',
    cmInInch: 2.54,
    codeCm:   'cm',
    codeInch: 'inch',
    translation: {
        memberSizeLabel: 'Suggested Size for',
        yourSizeLabel: 'Suggested Size',
        yourSizeUndefinedLabel: 'Suggested Size is undefined',
        yourSizeOutOfRangeLabel: 'Suggested Size is out of range!',
        sizeChartButtonLabel: 'Size Chart',             //todo: where is using?????
        sizeChartButtonImage: 'Size Chart'
    },
    sessionNameCurrent: 'ave_sizechart_dimension_main',
    setActiveUrl: null,
    setDimensionUrl: null,
    paramBaseUrl: null,
    sessionBaseUrl: null,
    isFocusedInput: false,
    isEnablePriority: false,
    sessionData: [],
    noNeedSaveDimensionCount: 0,
    initialize: function (params, isAjax) {
        /** todo:
         * create separate files for:
         * 1 create interface
         * 2 fill data
         * 3 calculate size
         */
        var sizechartInstance = this;
        if (isAjax) {
            new Ajax.Request(
                params.paramBaseUrl + 'getData', {
                    method: 'post',
                    parameters: {'productId': params.productId},
                    onSuccess: (function (data) {
                        data = data.responseText.evalJSON();
                        sizechartInstance.initParams(data);
                        sizechartInstance.startApp();
                    })
                }
            );
        } else {
            this.initParams(params);
            this.startApp();
        }
        this.getSuggestedLabel();
    },
    initParams: function(params) {
        this.sizes = params.sizes;
        this.isEnablePriority = params.isEnablePriority;
        this.dimensionList = params.dimensionList;
        this.setActiveUrl = params.setActiveUrl;
        this.setDimensionUrl = params.setDimensionUrl;
        this.sessionBaseUrl = params.sessionBaseUrl;
        this.sessionData = params.sessionData;
        this.members = params.members;  //todo: need to implement it, build dropdown from js, now is in template
        this.membersMeasurements = params.membersMeasurements;
        this.currentDimension = params.currentDimension;
        this.translation = params.translation;
    },
    initListeners: function () {
        var sizechartInstance = this;
        $$('select#active_member').each(
            function (element) {
                element.observe('change', sizechartInstance.changeMember);
            }
        );
        $$('.ave-sizechart-show-link').each(
            function (element) {
                element.observe('click', sizechartInstance.showPopup);
            }
        );
        $$('input.' + this.inputDimensionClass).each(
            function (element) {
                element.observe('change', sizechartInstance.changeUserDimension);
                element.observe('focus', sizechartInstance.onInputFocus);
            }
        );
        $$('.measurement_toggle .dimension_btn').each(
            function (element) {
                element.observe('click', sizechartInstance.changeMeasurement);
            }
        );
        document.onkeydown = function (e) {
            if (e.key === "Escape") {
                sizechartInstance.hidePopup();
            }
        };
    },
    showMainButton: function () {
        $$('.ave-sizechart-show-link').each(
            function (element) {
                element.removeClassName('hidden');
            }
        );
    },
    startApp: function () {
        //todo: need move to better place
        var holder = document.getElementById('ave-sizechart-popup-holder');
        if (holder && holder.parentNode) {
            holder.parentNode.removeChild(holder);
        }
        this.rebuildMemberDropdown();
        if (document.getElementById('ave-sizechart-button-label') != undefined) {
            this.sizeChartButtonImage = document.getElementById('ave-sizechart-button-label').innerHTML;
        }
        if (!this.sessionData[this.inputSelectDimensionName]) {
            this.setSession(this.inputSelectDimensionName, '');
        }
        this.showMainButton();
        this.initDimensionFormDbToSession();
        this.initDimensionFromSession();
        this.initBaseMeasurement();
        this.initListeners();
        this.highlightCellByDimensions();
        this.makeClickable();
    },
    makeClickable: function () {
        var sizechartInstance = this;
        $$('table.ave-sizechart-table td').each(
            function (td) {
                td.observe('click', sizechartInstance.onTdClick);
            }
        );
    },
    resetCurrentMember: function () {
        var memberSelectElement = $('active_member');
        if ((memberSelectElement != null) && (memberSelectElement.getValue() > 0)) {
            memberSelectElement.setValue(0);
        }
    },
    onTdClick: function () {
        ave_sizechart.resetCurrentMember();
        $$('.match-size').each(
            function (element) {
            element.removeClassName('match-size');
            }
        );
        $$('.sub-match-size').each(
            function (element) {
            element.removeClassName('sub-match-size');
            }
        );
        this.parentNode.addClassName('match-size');
        var td, tdId, size, input;
        if (this.parentNode.childNodes.length > 0) {
            for (var i = 0; i < this.parentNode.childNodes.length; i++) {
                td = this.parentNode.childNodes[i];
                tdId = td.id;
                for (var k in ave_sizechart.sizes){
                    input = $('ave_sizechart_dimension_' + k);
                    if (ave_sizechart.sizes.hasOwnProperty(k) && input) {
                        for (var sizeId in ave_sizechart.sizes[k]){
                            if (ave_sizechart.sizes[k].hasOwnProperty(sizeId) && sizeId == tdId) {
                                size = ave_sizechart.getAverageSize(ave_sizechart.sizes[k][sizeId]);
                                if (ave_sizechart.currentDimension == ave_sizechart.codeInch) {
                                    size = ave_sizechart.cmToInch(size);
                                }

                                input.setValue(size);
                                td.addClassName('match-size');
                                if (ave_sizechart.isNumber(size)) {
                                    ave_sizechart.setSession(input.name, size);
                                    ave_sizechart.highlightCellByDimensions();
                                    ave_sizechart.saveMemberDimension(k, size);
                                    input.removeClassName('ave-sizechart-error');
                                }
                            }
                        }
                    }
                }
            }
        }
    },
    showPopup: function () {
        var holder = document.getElementById('ave-sizechart-popup-holder'), holder_bg, container, close_btn, tableHolder;
        if (!holder) {
            holder = document.createElement('div');
            holder.setAttribute('id', 'ave-sizechart-popup-holder');

            holder_bg = document.createElement('div');
            holder_bg.addClassName('ave-sizechart-popup-holder-background');
            holder_bg.addClassName('animated');
            holder_bg.onclick = function () {
                ave_sizechart.hidePopup();
            };

            close_btn = document.createElement('div');
            close_btn.addClassName('ave-sizechart-popup-holder-closebutton');
            close_btn.innerHTML = '<a href="javascript:ave_sizechart.hidePopup();" title="close">&times;</a>';

            container = document.createElement('div');
            container.addClassName('ave-sizechart-popup-holder-content');

            document.getElementsByTagName('body')[0].appendChild(holder);
            holder.appendChild(holder_bg);
            holder.appendChild(container);
            container.appendChild(close_btn);
            tableHolder = document.getElementById('ave-sizechart-holder');
            container.appendChild(tableHolder);
        }

        holder.addClassName('opened');
        return false;
    },
    hidePopup: function () {
        if (document.getElementById('ave-sizechart-popup-holder')) {
            document.getElementById('ave-sizechart-popup-holder').removeClassName('opened');
        }
    },
    isNumber: function (n) {
        return !isNaN(parseFloat(n)) && isFinite(n);
    },
    setSession: function (cname, cvalue) {
        if (this.sessionData.hasOwnProperty(cname) && this.sessionData[cname] === cvalue) {
            return true;
        }
        this.sessionData[cname] = cvalue;
        new Ajax.Request(
            this.sessionBaseUrl + 'set', {
            method: 'post',
            parameters: {'name': cname, 'value': cvalue},
            onSuccess: (function (data) {
                /*data = data.responseText.evalJSON();*/
            })
            }
        );
        this.dimensions[cname] = cvalue;
    },
    getSession: function (cname) {
        var value = '';
        if (this.sessionData[cname] || this.sessionData[cname] === '') {
            value = this.sessionData[cname];
        }
        return value;
    },
    isCurrentDimensionInch: function () {
        return '' == this.getSession(this.inputSelectDimensionName) && this.currentDimension == this.codeInch
            || this.getSession(this.inputSelectDimensionName) == this.codeInch;
    },
    initBaseMeasurement: function () {
        if (this.isCurrentDimensionInch()) {
            this.currentDimension = this.codeInch;
            this.showInchSizes(this.codeInch);
        } else if (this.getSession(this.inputSelectDimensionName) == this.codeCm) {
            this.currentDimension = this.codeCm;
        }
    },
    rebuildMemberDropdown: function () {
        if (this.members && this.members.length > 0) {
            var data = this.members,
                memberSelectElement = $('active_member');
            if ((memberSelectElement != null)) {
                while (memberSelectElement.length > 1) {
                    memberSelectElement.removeChild(memberSelectElement.lastChild);
                }

                for (var i = 0; i < data.length; i++) {
                    var option = document.createElement('option');
                    option.innerHTML = data[i].name;
                    option.value = data[i].id;
                    if (data[i].active) {
                        option.selected = 'selected';
                    }
                    memberSelectElement.appendChild(option);
                }
            }
        }
    },
    initDimensionFormDbToSession: function () {
        var memberSelectElement = $('active_member'),
            ave_sizechart_instance = this,
            memberId, measurements, dimensionId, value;
        if ((memberSelectElement != null) && (memberId = memberSelectElement.getValue())) {
            measurements = ave_sizechart_instance.membersMeasurements[memberId];
            $$('input.' + ave_sizechart_instance.inputDimensionClass).each(
                function (element) {
                    dimensionId = element.id.replace(/[^\d.]/g, '');
                    if (typeof measurements != 'undefined' && measurements.hasOwnProperty(dimensionId)) {
                        value = ave_sizechart_instance.isCurrentDimensionInch()
                              ? ave_sizechart_instance.cmToInch(measurements[dimensionId])
                              : measurements[dimensionId];
                        ave_sizechart_instance.setSession(element.name, value);
                    } else {
                        ave_sizechart_instance.setSession(element.name, '');
                    }
                }
            );
        }
    },
    initDimensionFromSession: function () {
        var dimensionValue, ave_sizechart_instance = this;
        $$('input.' + ave_sizechart_instance.inputDimensionClass).each(
            function (element) {
            dimensionValue = ave_sizechart_instance.getSession(element.name);
            ave_sizechart_instance.dimensions[element.name] = dimensionValue;
            if (dimensionValue != "") {
                element.setValue(dimensionValue);
            }
            }
        );
        $$('.measurement_toggle .dimension_btn').each(
            function (element) {
            if (ave_sizechart_instance.getSession(ave_sizechart_instance.inputSelectDimensionName) != '') {
                if (ave_sizechart_instance.getSession(ave_sizechart_instance.inputSelectDimensionName) == element.getAttribute('data-code')) {
                    element.addClassName('active');
                } else {
                    element.removeClassName('active');
                }
            }
            }
        );
    },
    cmToInch: function (size) {
        if (size == 0) return 0;
        var fraction, sizeValue;
        sizeValue = (parseFloat(size) / this.cmInInch).toFixed(2);
        fraction = sizeValue % 1;
        sizeValue = parseInt(sizeValue) + Math.round(fraction * 4) / 4;
        return sizeValue;
    },
    inchToCm: function (size) {
        if (size == 0) return 0;
        var fraction, sizeValue;
        sizeValue = (parseFloat(size) * this.cmInInch).toFixed(2);
        fraction = sizeValue % 1;
        sizeValue = parseInt(sizeValue) + Math.round(fraction * 4) / 4;
        return sizeValue;
    },
    getAverageSize: function (size) {
        var averageValue, i, fraction, summarySize = 0;
        if (size.indexOf('-') != -1) {
            averageValue = size.split('-');
            for (i = 0; i < averageValue.length; i++) {
                summarySize += parseFloat(averageValue[i]);
            }

            averageValue = (summarySize / i).toFixed(2);
            fraction = averageValue % 1;
            averageValue = parseInt(averageValue) + Math.round(fraction * 4) / 4;
        } else {
            averageValue = parseFloat(size);
        }

        return averageValue;
    },
    getSize: function (size, measurement) {
        var sizeValue, i;
        if (size.indexOf('-') != -1) {
            sizeValue = size.split('-');
            for (i = 0; i < sizeValue.length; i++) {
                if (measurement == this.codeInch) {
                    sizeValue[i] = this.cmToInch(sizeValue[i]);
                } else {
                    sizeValue[i] = parseFloat(sizeValue[i]);
                }
            }

            sizeValue = sizeValue.join('-');
        } else {
            if (measurement == this.codeInch) {
                sizeValue = this.cmToInch(size);
            } else {
                sizeValue = parseFloat(size);
            }
        }

        return sizeValue;
    },
    showInchSizes: function (measurementCode) {
        var sizeValue, dimensionUserId, sizes, sizeId;
        for (var dimensionId in this.dimensions) {
            if (this.dimensions.hasOwnProperty(dimensionId)) {
                if (this.inputSelectDimensionName == dimensionId) {
                    continue;
                }

                dimensionUserId = dimensionId.split('_').last();
                sizes = this.sizes[dimensionUserId];
                for (sizeId in sizes) {
                    if (!sizes.hasOwnProperty(sizeId)) {
                        continue;
                    }

                    if (this.codeCm == measurementCode) {
                        sizeValue = this.getSize(sizes[sizeId]);
                    } else {
                        sizeValue = this.getSize(sizes[sizeId], measurementCode);
                    }

                    var el = document.getElementById(sizeId);
                    el.innerText = sizeValue;
                    el.textContent = sizeValue;
                }
            }
        }
    },
    changeMeasurement: function () {
        var measurementCode = this.getAttribute('data-code');
        $$('.measurement_toggle .dimension_btn').each(
            function (element) {
            if (measurementCode == element.getAttribute('data-code')) {
                element.addClassName('active');
            } else {
                element.removeClassName('active');
            }
            }
        );
        ave_sizechart.setSession(ave_sizechart.inputSelectDimensionName, measurementCode);
        ave_sizechart.currentDimension = measurementCode;
        ave_sizechart.showInchSizes(measurementCode);
        ave_sizechart.highlightCellByDimensions();
    },
    clearSelectedMatches: function (matchClass, subMatchClass, mainMatchClass) {
        var listMatchElements = [], listSubMatchElements = [], listMainMatchElements = [], i;
        for (i = 0; i < document.getElementsByClassName(mainMatchClass).length; i++) {
            listMainMatchElements.push(document.getElementsByClassName(mainMatchClass)[i]);
        }

        for (i = 0; i < document.getElementsByClassName(matchClass).length; i++) {
            listMatchElements.push(document.getElementsByClassName(matchClass)[i]);
        }

        for (i = 0; i < document.getElementsByClassName(subMatchClass).length; i++) {
            listSubMatchElements.push(document.getElementsByClassName(subMatchClass)[i]);
        }

        for (i = 0; i < listMainMatchElements.length; i++) {
            listMainMatchElements[i].removeClassName(mainMatchClass);
        }

        for (i = 0; i < listSubMatchElements.length; i++) {
            listSubMatchElements[i].removeClassName(subMatchClass);
        }

        for (i = 0; i < listMatchElements.length; i++) {
            listMatchElements[i].removeClassName(matchClass);
        }

        this.initDescriptionButton();
        document.getElementById('ave-sizechart-current-size').textContent = this.translation.yourSizeUndefinedLabel;
        document.getElementById('ave-sizechart-current-size').removeClassName('out-of-range');
        if (document.getElementById('ave-sizechart-button-label') != undefined) {
            document.getElementById('ave-sizechart-button-label').innerHTML = this.sizeChartButtonImage;
            $$('.ave-sizechart-show-link').invoke('removeClassName', 'button');
        }
    },
    initDescriptionButton: function (visible) {
        var descriptionButton = $$('.ave-sizechart-description .button');
        if (descriptionButton && descriptionButton.length > 0) {
            descriptionButton.each(
                function (item) {
                if (visible && visible != undefined) {
                    item.removeClassName('hidden');
                } else {
                    item.addClassName('hidden');
                }
                }
            );
        }
    },
    activateRecommendationProductSize: function (recSize) {
        recSize = recSize.trim().toLowerCase();
        var options = $$('#configurable_swatch_size a.swatch-link');
        for (var i = 0; i < options.length; i++) {
            if (options[i] && options[i].readAttribute('title') &&
                recSize == options[i].readAttribute('title').toLowerCase()) {
                options[i].click();
                break;
            }
        }
    },
    highlightCellByDimensions: function () {
        var dimensionUserValue, dimensionUserId, dimensionCurrentArray, sizeId, matchSizes, matchClass = 'match-size',
            subMatchClass = 'sub-match-size', mainMatchClass = 'main-match-size', priority = 0;
        this.clearSelectedMatches(matchClass, subMatchClass, mainMatchClass);
        function getMainSize(sizeId, priority, recommendationSizeByPriority)
        {
            var mainSize = document.getElementById(sizeId).parentElement.getElementsByClassName('ave-main');
            if (mainSize.length > 0) {
                mainSize[0].addClassName(mainMatchClass);
                var currentSize = '',
                    currentSizes = $$('#ave-sizechart-holder .ave-main.main-match-size'),
                    recommendationSize = '';
                if (currentSizes.length == 1) {
                    currentSize += currentSizes[0].textContent;
                    recommendationSize = currentSizes[0].textContent;
                } else if (currentSizes.length > 1) {
                    currentSize += currentSizes[0].textContent;
                    if (currentSizes[currentSizes.length - 1].textContent != currentSizes[0].textContent) {
                        currentSize += ' - ' + currentSizes[currentSizes.length - 1].textContent;
                        recommendationSize = currentSizes[currentSizes.length - 1].textContent;
                    }
                }
                if (recommendationSizeByPriority['priority'] == null || recommendationSizeByPriority['priority'] < priority) {
                    recommendationSizeByPriority['priority'] = priority;
                    recommendationSizeByPriority['size'] = recommendationSize;
                }
                return {'recommendationSize': recommendationSize, 'currentSize': currentSize,
                    'recommendationSizeByPriority': recommendationSizeByPriority};
            } else {
                return null;
            }
        }
        function showMainSize(currentSize, sizeByPriority, thiss)
        {
            if (thiss.isEnablePriority) {
                currentSize = sizeByPriority;
            }
            var yourSizeLabel = thiss.getSuggestedLabel(),
                buttonLabel = $('ave-sizechart-button-label'),
                currentSizeElement = $('ave-sizechart-current-size');

            currentSizeElement.innerHTML = yourSizeLabel + ' ' + currentSize;
            if (buttonLabel && buttonLabel != undefined) {
                buttonLabel.innerHTML = yourSizeLabel + ' ' + currentSize;
            }
            thiss.initDescriptionButton(true);
            thiss.setSession(thiss.sessionNameCurrent, currentSize);
        }
        var recommendationSize = [], mainSize = null, recommendationSizeByPriority = {'priority': null, 'size': null};
        for (var dimensionId in this.dimensions) {
            if (this.dimensions.hasOwnProperty(dimensionId)) {
                if (this.inputSelectDimensionName == dimensionId) {
                    continue;
                }
                dimensionUserValue = this.dimensions[dimensionId];
                dimensionUserId = dimensionId.split('_').last();
                dimensionCurrentArray = this.sizes[dimensionUserId];
                if (this.dimensionList['dimension_' + dimensionUserId] != null) {
                    priority = +this.dimensionList['dimension_' + dimensionUserId]['priority'];
                } else {
                    priority = 0;
                }
                matchSizes = this.getBestMatchIds(dimensionCurrentArray, dimensionUserValue);
                if (false === matchSizes) {                          //1 - didn't find any values
                } else if (matchSizes.hasOwnProperty('id')
                    && matchSizes.hasOwnProperty('value')) {        //2 - strict match
                    document.getElementById(matchSizes['id']).addClassName(matchClass);
                    document.getElementById(matchSizes['id']).parentElement.addClassName(matchClass);
                    mainSize = getMainSize(matchSizes['id'], priority, recommendationSizeByPriority);
                    if (mainSize) {
                        showMainSize(mainSize.currentSize, mainSize.recommendationSizeByPriority.size, this);
                        recommendationSize.push(mainSize.recommendationSize);
                        recommendationSizeByPriority = mainSize.recommendationSizeByPriority;
                    }
                } else if (matchSizes.hasOwnProperty('length') && (matchSizes.length == 1 || matchSizes.length == 2)) {
                                   //3 - find one value
                    for (sizeId in matchSizes) {
                        if (matchSizes.hasOwnProperty(sizeId) && sizeId != 'length') {
                            document.getElementById(sizeId).addClassName(subMatchClass);
                            document.getElementById(sizeId).parentElement.addClassName(subMatchClass);
                            mainSize = getMainSize(sizeId, priority, recommendationSizeByPriority);
                            if (mainSize) {
                                showMainSize(mainSize.currentSize, mainSize.recommendationSizeByPriority.size, this);
                                recommendationSize.push(mainSize.recommendationSize);
                                recommendationSizeByPriority = mainSize.recommendationSizeByPriority;
                            }
                        }
                    }
                }
            }
        }

        if (recommendationSize.length > 0) {
            /* select size value in Size attribute in dropdown */
            this.activateRecommendationProductSize(recommendationSize[recommendationSize.length - 1]);
            $$('.ave-sizechart-show-link').invoke('addClassName', 'button');
        } else {
            var sizeEntered = false;
            $$('input.' + this.inputDimensionClass).each(
                function (element) {
                    if (element.getValue() != undefined && element.getValue().length > 0) {
                        sizeEntered = true;
                    }
                }
            );
            if (sizeEntered) {
                document.getElementById('ave-sizechart-current-size').textContent = this.translation.yourSizeOutOfRangeLabel;
                document.getElementById('ave-sizechart-current-size').addClassName('out-of-range');
            }
        }
    },
    getBestMatchIds: function (sizes, userSize) {
        var defaultLeft = 0, left = defaultLeft, leftId, defaultRight = 100000, right = defaultRight, rightId, sizeValue,
            resultSizes = [], dimensionTax = 0, dimensionTaxLast = 0, realSizes = {}, sizeLength = 0, sizeId,
            userDimension = this.getSession(this.inputSelectDimensionName), minSize, maxSize, sizeValues;
        for (sizeId in sizes) {
            if (!sizes.hasOwnProperty(sizeId)) {
                continue;
            }

            if (userDimension == this.codeInch || this.currentDimension == this.codeInch) {
                sizeValue = this.getSize(sizes[sizeId], this.codeInch);
            } else {
                sizeValue = this.getSize(sizes[sizeId]);
            }

            if (('' + sizeValue).indexOf('-') != -1) {
                sizeValues = sizeValue.split('-');
                minSize = parseFloat(sizeValues[0]);
                maxSize = parseFloat(sizeValues[1]);
                if (minSize <= userSize && maxSize >= userSize) {
                    return {id: sizeId, value: userSize};
                } else if (minSize > userSize && minSize < right) {
                    right = minSize;
                    rightId = sizeId;
                } else if (maxSize < userSize && maxSize > left) {
                    left = maxSize;
                    leftId = sizeId;
                }

                if (dimensionTaxLast != 0 && sizeValue != 0) {
                    dimensionTax += minSize - dimensionTaxLast;
                }

                dimensionTaxLast = minSize;
            } else {
                if (sizeValue == userSize) {
                    return {id: sizeId, value: userSize};
                } else if (sizeValue > userSize && sizeValue < right) {
                    right = sizeValue;
                    rightId = sizeId;
                } else if (sizeValue < userSize && sizeValue > left) {
                    left = sizeValue;
                    leftId = sizeId;
                }

                if (dimensionTaxLast != 0 && sizeValue != 0) {
                    dimensionTax += sizeValue - dimensionTaxLast;
                }

                dimensionTaxLast = sizeValue;
            }

            sizeLength++;
        }

        if (sizeLength > 0) {
            dimensionTax = dimensionTax / sizeLength;
        }

        if (defaultLeft != left) {
            resultSizes[leftId] = left;
        }

        if (defaultRight != right) {
            resultSizes[rightId] = right;
        }

        //якщо введене значення не співпадає з граничним більше ніж на середнє збільшення
        for (sizeId in resultSizes) {
            if (!resultSizes.hasOwnProperty(sizeId)) {
                continue;
            }

            if ((resultSizes[sizeId] < userSize && (resultSizes[sizeId] + dimensionTax) < userSize)    //right
                || (resultSizes[sizeId] > userSize && (resultSizes[sizeId] - dimensionTax) > userSize)    //left
            ) {
                //do nothing
            } else {
                realSizes[sizeId] = resultSizes[sizeId];
                if (!realSizes.hasOwnProperty('length')) {
                    realSizes['length'] = 0;
                }

                realSizes['length']++;
            }
        }

        if (realSizes == {}) {
            return false;
        }

        return realSizes;
    },
    changeMember: function () {
        ave_sizechart.isFocusedInput = false;
        var memberId = this.getValue(),
            measurements = ave_sizechart.membersMeasurements[memberId];
        /* step 1: change values in dimension inputs */
        $$('input.' + ave_sizechart.inputDimensionClass).each(
            function (element) {
            var dimensionId = element.id.replace(/[^\d.]/g, ''),
                value = 0;
            if (typeof measurements != 'undefined' && measurements.hasOwnProperty(dimensionId)) {
                if (measurements[dimensionId]) {
                    value = ave_sizechart.isNumber(measurements[dimensionId]) ? parseFloat(measurements[dimensionId]) : 0;
                    value = ave_sizechart.isCurrentDimensionInch() ? ave_sizechart.cmToInch(value) : value;
                }
            }

            ave_sizechart.noNeedSaveDimensionCount++;
            element.value = value;
            if ("createEvent" in document) {
                var evt = document.createEvent("HTMLEvents");
                evt.initEvent("change", false, true);
                element.dispatchEvent(evt);
            } else {
                element.fireEvent("onchange");
            }
            }
        );
        /* step 2: set default member in db */
        new Ajax.Request(
            ave_sizechart.setActiveUrl, {
            method: 'post',
            parameters: {'member_id': memberId},
            onSuccess: (function (data) {
                /*data = data.responseText.evalJSON();*/
            })
            }
        );
    },
    saveMemberDimension: function (id, value) {
        if (ave_sizechart.noNeedSaveDimensionCount != 0) {
            return;
        }

        var memberSelectElement = $('active_member');
        if ((memberSelectElement != null) && (memberSelectElement.getValue() > 0)) {
            if (ave_sizechart.isCurrentDimensionInch()) {
                value = ave_sizechart.inchToCm(value);
            }

            if (!ave_sizechart.membersMeasurements.hasOwnProperty(memberSelectElement.getValue())) {
                ave_sizechart.membersMeasurements[memberSelectElement.getValue()] = [];
            }

            ave_sizechart.membersMeasurements[memberSelectElement.getValue()][id] = value;
            new Ajax.Request(
                ave_sizechart.setDimensionUrl, {
                method: 'post',
                parameters: {'dimension_id': id, 'value': value, 'member_id': memberSelectElement.getValue()},
                onSuccess: (function (data) {
                    /*data = data.responseText.evalJSON();*/
                })
                }
            );
        }
    },
    onInputFocus: function () {
        ave_sizechart.isFocusedInput = true;
    },
    changeUserDimension: function () {
        if (ave_sizechart.isFocusedInput) {
            ave_sizechart.resetCurrentMember();
        }
        var value = this.getValue();
        var errorClass = 'ave-sizechart-error';
        if (ave_sizechart.isNumber(value)) {
            ave_sizechart.setSession(this.name, value);
            ave_sizechart.highlightCellByDimensions();
            ave_sizechart.saveMemberDimension(this.id.replace(/[^\d.]/g, ''), value);
            this.removeClassName(errorClass);
        } else {
            this.addClassName(errorClass);
        }

        if (ave_sizechart.noNeedSaveDimensionCount > 0) {
            ave_sizechart.noNeedSaveDimensionCount--;
        }
    },
    getSuggestedLabel: function () {
        var memberName = document.getElementById('active_member'),
            label = '';
        if (typeof memberName !== 'undefined' && memberName !== null && memberName.selectedIndex > 0) {
            memberName = memberName.options[memberName.selectedIndex].text;
            label += this.translation.memberSizeLabel + ' ' + memberName;
        } else {
            label += this.translation.yourSizeLabel;
        }
        return '<span class="size-name-label">' + label + '</span>';
    }
};
