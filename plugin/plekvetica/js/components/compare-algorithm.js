"use strict";

function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance"); }

function _iterableToArray(iter) { if (Symbol.iterator in Object(iter) || Object.prototype.toString.call(iter) === "[object Arguments]") return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = new Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } }

var evaluation = function evaluation(charX, charY, costs) {
  return charX == charY ? costs.match : costs.mismatch;
}; // evaluation function


var createMatrix = function createMatrix(length1, length2) {
  return _toConsumableArray(new Array(length1)).map(function (a) {
    return _toConsumableArray(new Array(length2)).map(function (b) {
      return 1;
    });
  });
};

var initXAxis = function initXAxis(fields, value) {
  return fields.map(function (field, i) {
    return !i ? field.map(function (d) {
      return value;
    }) : field;
  });
};

var initYAxis = function initYAxis(fields, value) {
  return fields.map(function (xAxis) {
    return xAxis.map(function (yAxis, axisIndex) {
      return axisIndex === 0 ? value : 0;
    });
  });
};

var tracebackDirectionHash = ['d', 'u', 'l'];

var F = function F(i, j, ma, costs) {
  var tracebackPoint = ''; // console.log(ma.matches, i, j)
  // console.log(ma.sequence[i - 1], ma.pattern[j - 1])

  var matches = [+ma.matches[j - 1][i - 1] + evaluation(ma.sequence[i - 1], ma.pattern[j - 1], costs), +ma.matches[j - 1][i] + costs.gap, +ma.matches[j][i - 1] + costs.gap]; // Hole den besten Match

  var bestMatch = Math.max.apply(Math, matches); // Falls es mehrere gleichwertige Matches gibt und der diagonale dabei ist, wähle diesen aus, sonst wähle den
  // mit dem besten Match

  if (matches.filter(function (e) {
    return bestMatch === e;
  }).length > 1 && matches[0] === bestMatch && bestMatch > 0) tracebackPoint = tracebackDirectionHash[0]; // Diagonal
  else if (matches.filter(function (e) {
      return bestMatch === e;
    }).length == 1 && bestMatch > 0) tracebackPoint = tracebackDirectionHash[matches.indexOf(bestMatch)];else if (bestMatch <= 0) {
      tracebackPoint = 0;
      bestMatch = 0;
    }
  return [bestMatch, tracebackPoint];
};

var matrix = function matrix(seq, pattern) {
  return {
    'sequence': seq,
    'pattern': pattern,
    'matches': createMatrix(pattern.length + 1, seq.length + 1),
    'traceback': createMatrix(pattern.length + 1, seq.length + 1),
    'peak': {
      'cell': {},
      'value': 0
    },
    'initalizeMatrix': function initalizeMatrix() {
      this.matches = initXAxis(initYAxis(this.matches, 0), 0);
      this.traceback = initXAxis(initYAxis(this.matches, 0), 0);
      this.traceback[0][0] = 0;
    },
    'computeScore': function computeScore(costs) {
      var _this = this;

      var tracebackMatrix = this.traceback;
      this.traceback = tracebackMatrix.map(function (row, yIndex) {
        if (yIndex === 0) return row;
        return row.map(function (element, xIndex) {
          if (xIndex === 0) return element;
          var field = F(xIndex, yIndex, _this, costs);

          if (_this.peak['value'] <= field[0]) {
            _this.peak['cell'] = {
              x: +xIndex,
              y: +yIndex
            };
            _this.peak['value'] = field[0];
          }

          _this.matches[yIndex][xIndex] = field[0]; // match

          return field[1]; // traceback
        });
      });
    },
    'trace': [],
    'getTrace': function getTrace() {
      var it = 0;
      if(typeof 'assign' === 'function'){
            console.log("hat assign");
      }
      //var activeCell = Object.assign({}, this.peak['cell']);
      var activeCell = jQuery.extend({}, this.peak['cell']);

      while (this.traceback[activeCell['y']][activeCell['x']] !== 0) {
        if (it > 20) break;
        it += 1;

        if (this.traceback[activeCell['y']][activeCell['x']] === 'd') {
          this.trace.push({
            'seqChar': this.sequence[activeCell['x'] - 1],
            'patChar': this.pattern[activeCell['y'] - 1],
            'seqIndex': activeCell['x'],
            'patIndex': activeCell['y']
          });
          activeCell['x'] -= 1;
          activeCell['y'] -= 1;
        } else if (this.traceback[activeCell['y']][activeCell['x']] === 'u') {
          this.trace.push({
            'seqChar': "-",
            'patChar': this.pattern[activeCell['y'] - 1],
            'seqIndex': activeCell['x'],
            'patIndex': activeCell['y']
          });
          activeCell['x'] -= 1;
        } else if (this.traceback[activeCell['y']][activeCell['x']] === 'l') {
          this.trace.push({
            'seqChar': this.sequence[activeCell['x'] - 1],
            'patChar': '-',
            'seqIndex': activeCell['x'],
            'patIndex': activeCell['y']
          });
          activeCell['y'] -= 1;
        }
      }
    }
  };
};

var smith_waterman = function smith_waterman(text, pattern, costs) {
  var M = matrix(text, pattern);
  M.initalizeMatrix();
  M.computeScore(costs);
  M.getTrace();
  return M;
};