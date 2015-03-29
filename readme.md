#RsaTools

Some quickly hacked together tools for dumping/rebuilding RSA keys, for use in "Reconstructing RSA Private Keys from Random Key Bits" (http://cseweb.ucsd.edu/~hovav/dist/reconstruction.pdf)

a.out in the below is compiled from http://segher.ircgeeks.net/wak/wak.c (implementation of the algorithm in the above article)

#####Dump the corrupt key out in base64

php dump.php private.pem dump_corrupt_pem


#####Corrupt a key, pipe the correct parts to a.out, pipe the output from a.out into the rebuild script.. and dump it to private_rebuilt.pem

php dump.php private.pem | ./a.out | php rebuild.php > private_rebuilt.pem

####Complete Test

#####Generate a key
openssl genrsa -out private.pem 1024

#####Dump the corrupted key for a look
php dump.php private.pem dump_corrupt_pem
```
-----BEGIN RSA PRIVATE KEY-----
MIICXgIBAAKBgQDbcCnbJ5yZLn+pxjBx7A1Y3E4MeAXo234hqXD69puKz/Ur36+I
ZMv148MUXdjHJd9QllMz5Or1QTzKmp77RCmxfmNQC0FltyWcI9g9Wn1H5UscDSR/
z5c+FnOwq71s8KfW04K1fBxt3XkvEDkFhu6DFRQ2Z15gyXv/2WVCTbaD+wIDAQAB
AoGADwAACgAAYAAKDgAAUADgAAYAAAAPAEAAAAAAAAAAwAAAAAAAAAkAADAKAACg
AMAHAJAA8AAACAAAALAAAAAAAAAAAAEAAMAAAAoA4AAAAAAAAAAIAAAEAAIAAAAE
AAUAoABgAAwAAAIPAAAIAADgAAMABwAAAwAAANAAALAAAAACQQAAUAoAAMAABgAI
AAAEAJAAAABAAAAgAA4AAEAPAAALAEAJAAAAAgAAQAAAAAsAAAkEAFAAAOAEANAA
AAIFAHAAAkEAAABwAJALAHAAIAAPAAAAQAAHAAAGAAMAAAAABQAAAAAAAAAAAACA
BQAA8ADwAADgANAA8OAAsAAACAAAUAkNAAJBAAAACQAAEAAGAAAADQAAAgCwABAA
MGAAMADgAAAAkACQBQAAQKALAAsAAA4IAHAAAAAAoA0AcAAACQAAQABA8AECQA8A
ABAAwAAEAAAAgAAAAAAQgAAJAAAPAAAAACAAALANAAcAsAMAAAAAAFAACwAA8AAJ
AIAACQYABQAGAAAADwACQQDuQAFLyfEdx+D86Y8T8l+4NB+QmD6C2A/7SfnssEO1
mdSiu6QSVbN7/8P7sgbibV2QhisvQQzIaQxnRyRyT4+B
-----END RSA PRIVATE KEY-----
```
#####Dump the corrupt key, via a.out, and back into the rebuild script
php dump.php private.pem | ./a.out | php rebuild.php
```
-----BEGIN RSA PRIVATE KEY-----
MIICHgIBAAKBgQDbcCnbJ5yZLn+pxjBx7A1Y3E4MeAXo234hqXD69puKz/Ur36+I
ZMv148MUXdjHJd9QllMz5Or1QTzKmp77RCmxfmNQC0FltyWcI9g9Wn1H5UscDSR/
z5c+FnOwq71s8KfW04K1fBxt3XkvEDkFhu6DFRQ2Z15gyXv/2WVCTbaD+wIDAQAB
AoGBAL8Eh1r/w2DQyk6meFti6Ud2Fvbhr/tAvi5dSj0cWM/gMQKtKCbp32Y/ivx6
qQ7Kl/eVs/3TgvhVfq67JDOD5WnAoEYxdkPDqGVaf+SQogiS1YIJSLc+FEly8FiR
NIW1xKveZBiMD5niP0eheOMB7wZzBtf8pZOW13LSK9a6Y3LBAkEA91I6Z+fKYYav
aBHkJPyR2teBQ5hKLpAOLgtEX5Tbm91OGSXkHeL0a0gYNOqLVe/JJGxUCOzrBLfW
cNQCxVd14QJBAOMjc7+Xa1Z/5iWBT1tbBU+R934pNhQj5RyAsWVi1QR03y+U8jcE
hzWiSvAt8jq26u7VJfbnULXE8IhiZV+pPVsCQQDBXSnK6R+C5pGndS2BeXLzvcAT
wDJnezuK5kY70J9umjUgVk6myzW71u8OGD5+Hu6NKqD9JXaOBsmDB09vSPUhAkEA
33NsForHDQSsDC+HO09zDRGDUfkG+49IgN7WJfCxsT2spym3k4p/3bK4WEpLJ5n1
y1mjjJ+JlhiVrQZnxlE/SwIA
-----END RSA PRIVATE KEY-----
```
