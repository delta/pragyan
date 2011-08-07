/**
 * @author Abhishek Shrivastava i.abhi27 [at] gmail.com
 * @description Code to search the contents of files of given extentions for a particular keyword
 * @copyright (c) 2010 Abhishek Shrivastava
 */
#include <iostream>
#include <fstream>
#include <vector>
#include <algorithm>
#include <cstring>
#include <cstdio>
#include <string>
#include <cstdlib>
using namespace std;
string SEARCHKEY="transform(";
string DIRECTORY="/var/www/workspace/pragyan/codebase";
char VALIDEXT[][10]={".php"};
int VALIDEXTCOUNT=1;
int STEPSIZE=100;
#define VI vector<int>
#define VS vector<string>
#define pb push_back
#define PEEK(xx) {cout<<endl<<#xx<<"="<<xx;}
#define WATCH(xxx) {cout<<"\nWATCHING "<<#xxx<<"=";for(int xi=0;xi<xxx.size();xi++){cout<<endl<<xxx[xi]<<"|";}cout<<"#";}


long int counter=0,vacount=0,tacount=0;

int xfind(string str,char c)
{
 for(int i=0;i<str.size();i++)
 {
  if(str[i]==c)
   return i;
 }
 return -1;
}
int valid(string fname)
{
  for(int i=0;i<VALIDEXTCOUNT;i++)
  	if(fname.find(VALIDEXT[i])!=string::npos)
  		return 1;
  
  return 0;
}
string addSlash(string fname)
{
 if(fname.find(" ")==string::npos)
  return fname;
 for(int i=0;i<fname.size();i++)
 {
  if(fname.find(" ",i)==string::npos)
   break;
  int ind=fname.find(" ",i);
  fname.insert(ind,"\\");
  i=ind+1;
 }
 return fname;
}
VS makeUnique(VS arr)
{
 for(int i=0;i<arr.size();i++)
 {
  for(int j=i+1;j<arr.size();j++)
  {
   if(arr[i]==arr[j])
   {
    arr.erase(arr.begin()+j);
   }
  }
 }
 return arr;
}
void scan_and_save(string cd,string fname)
{

 system("touch tmp_file_scan");
 system("touch tmp_file_scan_var");
 fname=addSlash(fname);  //to add '\' before a space in the filename
 
 
//checking if the file type is valid
 ++tacount;
 if(!valid(fname))
 {
 // cout<<"\n!!!Invalid Filename!!!";
  return;
 }
 ++vacount;
 string md5sum1,md5sum2; //initializind md5sum variables
 fstream md5file;

 
 
 // calculating the md5sum of the file in the beginning
 system("md5sum tmp_file_scan > tmp_md5_sum");
 md5file.open("tmp_md5_sum",ios::in|ios::out);
 md5file>>md5sum1; //storing the md5sum into a variable md5sum1
 md5file.close();
 system("rm tmp_md5_sum"); //removing the temporary file

 
 //executing the 'grep mysql_connectdb' command on our target file
 string cmd="";
 cmd+="cat "+cd+"/"+fname+"|grep \""+SEARCHKEY+"\" >> tmp_file_scan";
 //cout<<"\nCMD="<<cmd;
 system(cmd.c_str()); 

 
/* The following script is required when searching for keywords that may be stored in php variables like $abc=keyword */
 
/* 
 //executing the 'grep [$]' to find $db variables (if any)
 cmd="";
 cmd+="cat tmp_file_scan|grep [$]>tmp_file_scan_var";
 system(cmd.c_str());


 //opening the tmp_file_scan_var file which contains the lines having [$] variables in them
 fstream file("tmp_file_scan_var",ios::in|ios::out);
 VS tmpdata;
 //storing the tmp_file_scan_var file as a vector<string> tmpdata
 while(file)
 {
  string str;
  char tmpstr[1000];
  file.getline(tmpstr,1000);
  str=tmpstr;
  tmpdata.pb(str);
 }

 //vars will contain ONLY the variables names e.g. mysql_connectdb($db3) -> db3
 VS vars;
 for(int i=0;i<tmpdata.size()-1;i++)
 {
  if(tmpdata[i].find("$")==string::npos)
   continue;
  int sind=tmpdata[i].find_first_of("$",0);
  int eind=tmpdata[i].find_first_of(")",sind+1);
  vars.pb(tmpdata[i].substr(sind+1,eind-sind-1));
 }

 //now searching for occurence of those "vars" variables in the main file again
 for(int j=0;j<vars.size();j++) 
 {
  cmd="";
  cmd+="cat "+cd+"/"+fname+"|grep [$]"+vars[j]+" >> tmp_file_scan";
  system(cmd.c_str());
 }
   file.close();
*/


 //now tmp_file_scan containes all the occurences of mysql_connectdb and [$] variables and few repetitions
 
 //now calculating the md5sum of tmp_file_scan again to see if something was found or not
 system("md5sum tmp_file_scan > tmp_md5_sum");
 md5file.open("tmp_md5_sum",ios::in|ios::out);
 md5file>>md5sum2;
 md5file.close();
 system("rm tmp_md5_sum");

 
 if(md5sum1!=md5sum2) //if something was found !
 {
  string cmd="echo "+cd+"/"+fname+" >> tmp_file_scan"; //append the filename to it
  system(cmd.c_str());
  char tc[10];
  sprintf(tc,"%d",counter);
  string tcc=tc;
  
  system(("echo -e '------------------------------------------------------"+tcc+"\n\n' >> tmp_file_scan").c_str());
 
 
  //removing repetitions
  fstream elimfile("tmp_file_scan",ios::in|ios::out);
  VS content;
  while(elimfile)
  {
   string str;
   char tmpstr[1000];
   elimfile.getline(tmpstr,1000);
   str=tmpstr;
   content.pb(str);
  }
  content=makeUnique(content);
  elimfile.close();

  system("rm tmp_file_scan");
  system("touch tmp_file_scan");
  elimfile.open("tmp_file_scan",ios::in|ios::out);
  for(int i=0;i<content.size();i++)
  {
   elimfile<<content[i]<<endl;
  }
  //storing the contents of tmp_file_scan after removing repetitions to the main dblistfile
  system("cat tmp_file_scan >> dblistfile");
  ++counter;
 }
  
 

  
  
}
int main()
{
 //cout<<"Click and key to start the process";
 cout<<"\nStarting process...";
 counter=0;
 system("rm tmp_db_file");
 string com="ls "+DIRECTORY+" -R > tmp_db_file";
 system(com.c_str());
 cout<<"\ntmp_db_file successfully created!";
 system("rm tmp_file_scan");
 system("rm dblistfile");

 system("rm tmp_file_scan_var");
 fstream file("tmp_db_file",ios::in|ios::out);
 VS mainlist;
 //cout<<"\nProceeding further.. Click and key!";
 while(file)
 {
  string fname;
  char tmpstr[1000]; 
  file.getline(tmpstr,1000);
  fname=tmpstr;
  mainlist.pb(fname);
 }
 cout<<"\ntmp_db_file successfully stored as a vector!";
 string cd=mainlist[0];
 for(int i=1;i<mainlist.size()-1;i++)
 {
  cd.erase(cd.size()-1);
  while(1)
  {
   if(mainlist[i][0]=='/' || i==mainlist.size()-1)
    break;
   else
   {
    //cout<<"\n\nProcessing="<<mainlist[i];
     scan_and_save(cd,mainlist[i]);
    if(tacount%STEPSIZE==0)
    {
     cout<<"\nFiles encountered="<<tacount<<endl<<"Files valid="<<vacount<<endl<<"Files having SEARCHKEY="<<counter;
    }
   
    ++i;
   }
  }
  cd=mainlist[i];
 }
 file.close();
 cout<<"\n\n!!!Process Completed. Results are stored in dblistfile!!!";
 cout<<"\n\nTotal Files Encountered: "<<tacount<<endl<<"Total Valid Files Scanned: "<<vacount<<endl;
  
 return 0;
}
  
  
 
