#!

# run from base directory 

curl -s 'https://docs.google.com/spreadsheet/pub?key=0AioLmDWIl9SxdDNhR3dJckZXbnRYb2FFUFVNSHI1YUE&single=true&gid=0&output=csv' > var/events.csv
curl -s 'https://docs.google.com/spreadsheet/pub?key=0AioLmDWIl9SxdDNhR3dJckZXbnRYb2FFUFVNSHI1YUE&single=true&gid=1&output=csv' > var/places.csv
curl -s 'https://docs.google.com/spreadsheet/pub?key=0AioLmDWIl9SxdDNhR3dJckZXbnRYb2FFUFVNSHI1YUE&single=true&gid=2&output=csv' > var/subjects.csv
lib/Grinder/bin/grinder --config etc/vfringe2013.cfg > htdocs/vfringe2013.rdf

