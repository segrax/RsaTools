#RsaTools

Some quickly hacked together tools for dumping/rebuilding RSA keys, for use in "Reconstructing RSA Private Keys from Random Key Bits" (http://cseweb.ucsd.edu/~hovav/dist/reconstruction.pdf)

a.out in the below is compiled from http://segher.ircgeeks.net/wak/wak.c (implementation of the algorithm in the above article)

Dump the corrupt key out in base64

php dump.php private.pem dump_corrupt_pem


Corrupt a key, pipe the correct parts to a.out, pipe the output from a.out into the rebuild script.. and dump it to private_rebuilt.pem

php dump.php private.pem | ./a.out | php rebuild.php > private_rebuilt.pem
