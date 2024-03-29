# uploadXML.py
# Uploads all of the XML files located in ./output to he exist database server

import httplib
import base64
import sys
import os
from string import rfind
import nyhconfig


def putFile(file):
	collection = '/exist/rest/db/apps/nyheritage/data'
	f = open(file, 'r')
	print 'Reading file %s ...' % file
	xml = f.read()
	f.close()

	p = rfind(file, '/')
	if p > -1:
		doc = file[p+1:]
	else:
		doc = file

	print doc
	print 'Storing document in collection %s ... ' % collection

	auth = base64.encodestring('%s:%s' % (nyhconfig.existAuth()['user'], nyhconfig.existAuth()['password'])).replace('\n', '')

	con = httplib.HTTP('18.204.203.159:8080')

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

# For each file in the output folder, call putfile
for filename in os.listdir('/home/ubuntu/nyh_scripts/Tealcat/output'):
	if filename.endswith('.xml'):
		#print os.path.join('./output/', filename)
		putFile('/home/ubuntu/nyh_scripts/Tealcat/output/' + filename)
