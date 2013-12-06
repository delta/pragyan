/**
 * @package pragyan
 * @copyright (c) 2008 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
/** gets all undelivered mail ids in format 'a@b.c','d.e@f.ed' ... from the mail file.
 * that can be directly in DELETE query.
 *
*/
/*file chk2 is output of following command : 
grep  -C 1  "<.*@.*>" sahil   | grep user -C 1  | grep "<*>" > chk2



*/
#include <stdio.h>
#include <string.h>
int main() {
FILE *f;
int i;
char *mail_new;
char mail_old[100]="clean email";
f=fopen("chk2","r");
for(i=0;i<41;i++) /* 431 is num of lines in chk2 -edited*/
   {
	fgets(mail_old,100,f); /* 100 is the max num of char to fetch */
	strtok(mail_old,">");
	mail_new=mail_old+1;
	printf("\'%s\',",mail_new);
   }
fclose(f);
}
