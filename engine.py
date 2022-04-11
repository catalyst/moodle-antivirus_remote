#!/usr/bin/env python

from flask import Flask, request
from os import path, remove, mkdir

app = Flask(__name__)
app.config['UPLOAD_FOLDER'] = '/var/lib/scanfiles'

@app.route("/conncheck")
def conncheck():
    return {
       "status": "OK",
    }

@app.route("/scan", methods=['POST'])
def scan():
    f = request.files['scanfile']
    f.save(app.config['UPLOAD_FOLDER'] + '/scantarget')

    #Call whatever on /var/lib/scanfiles/scantarget
    #subprocess.call('scanme');

    virus = True
    error = False

    remove(app.config['UPLOAD_FOLDER'] + '/scantarget');

    if virus and not error:
        return {
            "status": "FOUND",
            "msg": "Very naughty content found."
        }
    else:
        return {
            "status": "ERROR",
            "msg": "something blew up"
        }

if __name__ == '__main__':
    if not (path.exists(app.config['UPLOAD_FOLDER'])):
        mkdir(app.config['UPLOAD_FOLDER'])
    app.run(host="localhost", port=8001, debug=True)