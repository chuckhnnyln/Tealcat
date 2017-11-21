# NYHPut.py
import httplib
import base64
import sys
from string import rfind

collection = '/exist/rest/db/apps/nyheritage/data'
file = sys.argv[1]
f = open(file, 'r')
print 'Reading file %s ...' % file
xml = f.read()
f.close()

p = rfind(file, '/')
if p > -1:
	doc = file[p+1]
else:
	doc = file
	
print doc
print 'Storing document in collection %s ... ' % collection
<<<<<<< HEAD
username = 'nyhrest'
password = 'anywherebuthere'
=======
username = '***removed***'
password = '***removed***'
>>>>>>> 5daabcd64c0a97906bf7570d09b25df3e038f38c

auth = base64.encodestring('%s:%s' % (username, password)).replace('\n', '')

con = httplib.HTTP('54.174.162.83:8080')

con.putrequest('PUT', '%s/%s' % (collection, doc))
con.putheader('Content-Type', 'application/xml')
clen = len(xml)
con.putheader('Content-Length', clen)
con.putheader('User-Agent', 'Python http auth')
con.putheader('Authorization', 'Basic %s' % auth)

con.endheaders()
con.send(xml)

errcode, errmsg, headers = con.getreply()

print 'Server responded with code %s: %s' % (errcode, errmsg)
