#! /usr/bin/env python
import MySQLdb
db1="pragyan11";
db2="pragyan11testing";
conn1 = MySQLdb.connect(host="localhost", user="pragyan11", passwd="andromeda", db=db1);
conn2 = MySQLdb.connect(host="localhost", user="pragyan11", passwd="andromeda", db=db2);
print "Comparing databases "+db1+" and "+db2;
tablenotfound1= "Tables Found in "+db1+" But Not in "+db2+":\nTable\t\t\t\tDeltaindex\n";
missingfields1= "Fields Missing:\nTable\t\tIn "+db1+"\tIn "+db2+"\t\tMissing field\n";
shuffledfields1= "Fields shuffled:\n";
fieldvaluechanged1= "Fields whose values have been changed:\n\n";
fieldproperty=("FIELD","TYPE","COLLATION","ATTRIBUTES","NULL","DEFAULT","EXTRA","ACTION");
tablenotfound2= "Tables Found in "+db2+" But Not in "+db1+":\nTable\t\t\t\t"+db2+" index\n";
missingfields2= "Fields Missing:\nTable\t\tIn "+db1+"\tIn "+db2+"\t\tMissing field\n";
shuffledfields2= "Fields shuffled:\n";
fieldvaluechanged2= "Fields whose values have been changed:\n\n";

cursor1 = conn1.cursor();
cursor2 = conn2.cursor();
sql = "SHOW TABLES";
cursor1.execute(sql);
cursor2.execute(sql);
tablelist1 = cursor1.fetchall();
tablelist2 = cursor2.fetchall();
allow=0;
if(len(tablelist1)>=len(tablelist2)):
	for i in range(len(tablelist1)):
	 for n in range(len(tablelist2)):
	  if tablelist1[i][0]==tablelist2[n][0]:
	   	allow=1;
	   	break;
	 if allow==0:
	  	tablenotfound1+=tablelist1[i][0]+"\t\t\t"+str(i+1)+"\n";
	        continue;
	 if allow==1:
	  allow=0;
	  query= "DESCRIBE "+tablelist1[i][0];
	  cursor1.execute(query);
	  cursor2.execute(query);
	  result1=cursor1.fetchall();
	  result2=cursor2.fetchall();
	  for j in range(max(len(result1),len(result2))):
	   for k in range(min(len(result1),len(result2))):
	     if(len(result1))>=len(result2):
	      if result1[j][0]==result2[k][0]:
	        if j!=k:
		 shuffledfields1+="In table "+tablelist1[i][0]+ " the field "+result1[j][0]+" is at "+str(j)+"th position in "+db1+" but "+str(k)+"th position in "+db2+" \n";
		for m in range(len(result1[j])):
	      	   if result1[j][m]!=result2[k][m]: fieldvaluechanged1+= "TABLE: "+tablelist1[i][0]+"\nFIELD: "+result1[j][0]+"\nFIELD PROPERTY: "+fieldproperty[m]+"\nVALUE IN "+db1+": "+result1[j][m]+" \nVALUE IN "+db2+": "+result2[k][m]+"\n\n";
		break;
	      if result1[j][0]!=result2[k][0]:
		if k==min(len(result1),len(result2))-1:
		   missingfields1+=tablelist1[i][0]+"\t"+str(len(result1))+"\t\t"+str(len(result2))+"\t\t"+result1[j][0]+"\n";

	     if(len(result1))<len(result2):
	      if result2[j][0]==result1[k][0]:
	        if j!=k:
		 shuffledfields1+="In table "+tablelist1[i][0]+ " the field "+result1[k][0]+"is at "+str(k)+"th position in "+db1+" but "+str(j)+"th position in "+db2+"\n";
		for m in range(len(result2[j])):
	      	   if result2[j][m]!=result1[k][m]: fieldvaluechanged1+= "TABLE: "+tablelist1[i][0]+"\nFIELD: "+result2[j][0]+"\nFIELD PROPERTY: "+fieldproperty[m]+"\nVALUE IN "+db1+": "+result1[k][m]+" \nVALUE IN "+db2+": "+result2[j][m]+"\n\n";
		break;
	      if result2[j][0]!=result1[k][0]:
		if k==min(len(result1),len(result2))-1:
		   missingfields1+=tablelist1[i][0]+"\t"+str(len(result1))+"\t\t"+str(len(result2))+"\t\t"+result2[j][0]+"\n";

print tablenotfound1;
print missingfields1;
print shuffledfields1;
print fieldvaluechanged1;

if(len(tablelist1)<len(tablelist2)):
	for i in range(len(tablelist2)):
	 for n in range(len(tablelist1)):
	  if tablelist2[i][0]==tablelist1[n][0]:
	   	allow=1;
	   	break;
	 if allow==0:
	  	tablenotfound2+=tablelist2[i][0]+"\t\t\t"+str(i+1)+"\n";
	        continue;
	 if allow==1:
	  allow=0;
	  query= "DESCRIBE "+tablelist2[i][0];
	  cursor1.execute(query);
	  cursor2.execute(query);
	  result1=cursor1.fetchall();
	  result2=cursor2.fetchall();
	  for j in range(max(len(result1),len(result2))):
	   for k in range(min(len(result1),len(result2))):
	     if(len(result1))>=len(result2):
	      if result1[j][0]==result2[k][0]:
	        if j!=k:
		 shuffledfields2+="In table "+tablelist2[i][0]+ " the field "+result1[j][0]+" is at "+str(j)+"th position in "+db1+" but "+str(k)+"th position in "+db2+" \n";
		for m in range(len(result1[j])):
	      	   if result1[j][m]!=result2[k][m]: fieldvaluechanged2+= "TABLE: "+tablelist2[i][0]+"\nFIELD: "+result1[j][0]+"\nFIELD PROPERTY: "+fieldproperty[m]+"\nVALUE IN "+db1+": "+result1[j][m]+" \nVALUE IN "+db2+": "+result2[k][m]+"\n\n";
		break;
	      if result1[j][0]!=result2[k][0]:
		if k==min(len(result1),len(result2))-1:
		   missingfields2+=tablelist1[i][0]+"\t"+str(len(result1))+"\t\t"+str(len(result2))+"\t\t"+result1[j][0]+"\n";

	     if(len(result1))<len(result2):
	      if result2[j][0]==result1[k][0]:
	        if j!=k:
		 shuffledfields1+="In table "+tablelist2[i][0]+ " the field "+result1[k][0]+"is at "+str(k)+"th position in "+db1+" but "+str(j)+"th position in "+db2+"\n";
		for m in range(len(result2[j])):
	      	   if result2[j][m]!=result1[k][m]: fieldvaluechanged1+= "TABLE: "+tablelist2[i][0]+"\nFIELD: "+result2[j][0]+"\nFIELD PROPERTY: "+fieldproperty[m]+"\nVALUE IN "+db1+": "+result1[k][m]+" \nVALUE IN "+db2+": "+result2[j][m]+"\n\n";
		break;
	      if result2[j][0]!=result1[k][0]:
		if k==min(len(result1),len(result2))-1:
		   missingfields1+=tablelist2s[i][0]+"\t"+str(len(result1))+"\t\t"+str(len(result2))+"\t\t"+result2[j][0]+"\n";

print tablenotfound2;
print missingfields2;
print shuffledfields2;
print fieldvaluechanged2;

conn1.close();
conn2.close();
