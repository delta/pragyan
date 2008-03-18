/// var objChoices: Holds the choices entered by the user for objective questions
/// var objRightAnswers: Holds the right answers checked by the user for objective questions
/// var txtRightAnswer: Holds the hint entered by the user for subjective questions
if(typeof objChoices == 'undefined') objChoices = new Array();
if(typeof objRightAnswers == 'undefined') objRightAnswers = new Array();
if(typeof subjRightAnswers == 'undefined') subjRightAnswers = new Array();


function getOptionsRows(optionType, ignoreCached, index) {
	var n = 5;
	if(!ignoreCached && (typeof objChoices[index] != 'undefined')) {
		n = objChoices[index].length;
	}

	var optionsRows = '';
	var typeArray = (optionType == 'radio' ? '' : '[]');

	for(var i = 0; i < n; i++) {
		optionsRows += '<tr><td nowrap="nowrap"><input name="optCorrectAnswer'+index+typeArray+'" type="' + optionType + '" value="' + i + '" ';
		if(!ignoreCached && typeof objRightAnswers[index] != 'undefined' && objRightAnswers[index][i] == true) {
			optionsRows += 'checked="checked" ';
		}
		optionsRows += '/>' +
						'</td><td><input type="text" name="txtQuestionOption'+index+'[]" value="';
		if(!ignoreCached && (typeof objChoices[index] != 'undefined') && i < objChoices[index].length) {
			optionsRows += objChoices[index][i];
		}
		optionsRows += '" /></td></tr>';
	}

	return optionsRows;
}

function addOptionsRows(optionType, containerTable, index) {
	containerTable.innerHTML += getOptionsRows(optionType, true, index);
}

function insertAfter(parent, node, referenceNode) {
  parent.insertBefore(node, referenceNode.nextSibling);
}


var previousQuestionType = new Array();

function QuestionTypeChanged(newType, containerTable, index) {
	if(typeof previousQuestionType[index] != 'undefined' && previousQuestionType[index] == 'subjective') {
		subjRightAnswers[index] = document.getElementById('txtRightAnswer' + index).value;
	}
	else if(typeof previousQuestionType[index] != 'undefined' && previousQuestionType == 'singleselectobjective' || previousQuestionType == 'multiselectobjective') {
		objChoices[index] = new Array();
		objRightAnswers[index] = new Array();
		var objType = previousQuestionType == 'singleselectobjective' ? 'radio' : 'checkbox';

		choiceBoxes = containerTable.getElementsByTagName('input');
		for(var i = 0; i < choiceBoxes.length; i++) {
			if(choiceBoxes[i].type == 'text') {
				objChoices[index][objChoices[index].length] = choiceBoxes[i].value;
			}
			else if(choiceBoxes[i].type == objType) {
				objRightAnswers[index][objRightAnswers[index].length] = choiceBoxes[i].checked;
			}
		}
	}
	previousQuestionType[index] = newType;


 	switch(newType) {
		case 'singleselectobjective':
		case 'multiselectobjective':
			var innerHtml = '<tr><th colspan="2">Objective Question Options</td>' +
					getOptionsRows(newType == 'singleselectobjective' ? 'radio' : 'checkbox', false, index);
			containerTable.innerHTML = innerHtml;
			innerHtml = 'Enter the options in the fields above, and check the correct answer(s)<br />' +
					'<input type="button" onclick="addOptionsRows(\'' + (newType == 'singleselectobjective' ? 'radio' : 'checkbox') + '\', document.getElementById(\'' +
					containerTable.id + '\', ' + index + '))" value="Add More Options" />';
			if(!document.getElementById('addMoreButtonHolder' + index)) {
				var spanObject = document.createElement('span');
				spanObject.id = 'addMoreButtonHolder' + index;
				spanObject.innerHTML = innerHtml;
				insertAfter(containerTable.parentNode, spanObject, containerTable);
			}
			else {
				spanObject = document.getElementById('addMoreButtonHolder' + index);
				spanObject.innerHTML = innerHtml;
			}
		break;

		case 'subjective':
			var innerHtml = '<tr><td><label for="txtRightAnswer' + index + '">Right Answer:</label><br />(Only as a hint while correction)</td>' +
					'<td><textarea name="txtRightAnswer' + index + '" id="txtRightAnswer' + index + '" rows="5" cols="50">';
			if(typeof subjRightAnswers[index] != 'undefined') {
				innerHtml += subjRightAnswers[index];
			}
			innerHtml += '</textarea></td></tr>';
			containerTable.innerHTML = innerHtml;
			spanObject = document.getElementById('addMoreButtonHolder' + index);
			if(spanObject) {
				spanObject.parentNode.removeChild(spanObject);
			}
		break;

		default:
			containerTable.innerHTML = '';
	}
}

