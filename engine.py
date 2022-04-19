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
    file = request.files['scanfile']
    filename = request.form['filename']
    userid = request.form['userid']
    file.save(app.config['UPLOAD_FOLDER'] + '/scantarget')

    #Call whatever on /var/lib/scanfiles/scantarget
    #subprocess.call('scanme');

    #Manual eicar detection to test the integration.
    if 'eicar' in filename.lower():
        virus = True
        error = False
    else:
        virus = False
        error = False

    remove(app.config['UPLOAD_FOLDER'] + '/scantarget');

    if virus:
        # TODO Log userid here. Improve returned message with output of subprocess.
        return {
            "status": "FOUND",
            "msg": "Very naughty content found."
        }
    elif error:
        return {
            "status": "ERROR",
            "msg": "something blew up"
        }
    else:
        return {
            "status": "OK",
            "msg": ""
        }

if __name__ == '__main__':
    if not (path.exists(app.config['UPLOAD_FOLDER'])):
        mkdir(app.config['UPLOAD_FOLDER'])
    app.run(host="localhost", port=8001, debug=True)