#! /usr/bin/python2
import plivo
import sys

number = sys.argv[1]
message = ""
for i in range(2,len(sys.argv)):
	message += sys.argv[i]+" "
f = open(".ht_sms_keys", "r")
auth = f.read().strip("\n\r").split(",")
print auth
p=plivo.RestAPI(auth[0],auth[1])
paras = {'src':'2131231', 'dst':number, 'text':message}
print "sending"
p.send_message(paras)
