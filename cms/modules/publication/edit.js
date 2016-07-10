/* Function to move the publications up and down */

function reorder(num , direction){
	var text1 = document.getElementById('p'+ String(num));
	var text2 = document.getElementById('p'+String((num+direction)));
	var temp;
	
	/*Swap*/
	temp= text1.value;
	text1.value = text2.value;
	text2.value = temp;
}
