from flask import Flask, jsonify
from flask_sock import Sock

app = Flask(__name__)
sock = Sock(app)

@app.route("/conncheck")
def conncheck():
    return {
        "status": "OK"
    }

@sock.route("/scan")
def scan(socket):
    # Do the scan.
    file = socket.receive();

    naughty = True
    msg = "Very naughty content"
    error = False

    if naughty and not error:
        socket.send(jsonify({
            "status": "FOUND",
            "msg": msg
        }))
    else:
        socket.send(jsonify({
            "status": "ERROR"
        }))

    socket.close()

if __name__ == '__main__':
    app.run(host="localhost", port=8000, debug=True)