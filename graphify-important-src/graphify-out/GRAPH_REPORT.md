# Graph Report - SplitMate-api/graphify-important-src  (2026-05-03)

## Corpus Check
- 32 files · ~18,796 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 235 nodes · 357 edges · 17 communities detected
- Extraction: 89% EXTRACTED · 11% INFERRED · 0% AMBIGUOUS · INFERRED: 39 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- [[_COMMUNITY_Community 0|Community 0]]
- [[_COMMUNITY_Community 1|Community 1]]
- [[_COMMUNITY_Community 2|Community 2]]
- [[_COMMUNITY_Community 3|Community 3]]
- [[_COMMUNITY_Community 4|Community 4]]
- [[_COMMUNITY_Community 5|Community 5]]
- [[_COMMUNITY_Community 6|Community 6]]
- [[_COMMUNITY_Community 7|Community 7]]
- [[_COMMUNITY_Community 8|Community 8]]
- [[_COMMUNITY_Community 9|Community 9]]
- [[_COMMUNITY_Community 10|Community 10]]
- [[_COMMUNITY_Community 11|Community 11]]
- [[_COMMUNITY_Community 12|Community 12]]
- [[_COMMUNITY_Community 13|Community 13]]
- [[_COMMUNITY_Community 14|Community 14]]
- [[_COMMUNITY_Community 22|Community 22]]
- [[_COMMUNITY_Community 23|Community 23]]

## God Nodes (most connected - your core abstractions)
1. `ExpenseController` - 38 edges
2. `ApiPayload` - 33 edges
3. `StatementRecord` - 24 edges
4. `BalanceService` - 18 edges
5. `GroupController` - 17 edges
6. `ExpenseController` - 14 edges
7. `Group` - 12 edges
8. `AuthController` - 12 edges
9. `SettlementController` - 10 edges
10. `User` - 7 edges

## Surprising Connections (you probably didn't know these)
- None detected - all connections are within the same source files.

## Communities

### Community 0 - "Community 0"
Cohesion: 0.08
Nodes (3): ApiPayload, AuthController, ExpenseController

### Community 1 - "Community 1"
Cohesion: 0.13
Nodes (1): ExpenseController

### Community 2 - "Community 2"
Cohesion: 0.19
Nodes (2): GroupInvitation, GroupController

### Community 3 - "Community 3"
Cohesion: 0.22
Nodes (1): BalanceService

### Community 4 - "Community 4"
Cohesion: 0.13
Nodes (1): StatementRecord

### Community 5 - "Community 5"
Cohesion: 0.15
Nodes (1): Group

### Community 6 - "Community 6"
Cohesion: 0.27
Nodes (1): SettlementController

### Community 7 - "Community 7"
Cohesion: 0.25
Nodes (1): User

### Community 8 - "Community 8"
Cohesion: 0.43
Nodes (1): StatementController

### Community 9 - "Community 9"
Cohesion: 0.29
Nodes (1): Settlement

### Community 10 - "Community 10"
Cohesion: 0.33
Nodes (1): GroupMemberController

### Community 11 - "Community 11"
Cohesion: 0.33
Nodes (1): Expense

### Community 12 - "Community 12"
Cohesion: 0.4
Nodes (1): WalletSnapshot

### Community 13 - "Community 13"
Cohesion: 0.5
Nodes (1): BalanceState

### Community 14 - "Community 14"
Cohesion: 0.5
Nodes (1): BalanceController

### Community 22 - "Community 22"
Cohesion: 0.67
Nodes (1): EnsureGroupMember

### Community 23 - "Community 23"
Cohesion: 0.67
Nodes (1): EnsureAdminAuthenticated

## Knowledge Gaps
- **Thin community `Community 1`** (37 nodes): `ExpenseController.php`, `ExpenseController`, `.calculateBalances()`, `.calculateExpenseDetails()`, `.calculateSettlementDetails()`, `.consolidateDebts()`, `.copyBalances()`, `.createDetailedExpenseStatement()`, `.createDetailedSettlementStatement()`, `.createDetailedStatement()`, `.createStatementRecords()`, `.debugBalance()`, `.debugBreakdowns()`, `.formatBalancesForDisplay()`, `.getBalancesAfter()`, `.getBalancesBefore()`, `.getDebtChanges()`, `.getPaymentSuggestions()`, `.getWhoOwesWhom()`, `.index()`, `.processExpense()`, `.processSettlement()`, `.regenerateSimplifiedStatements()`, `.simulateCase1()`, `.simulateCase10_TimeBasedSequence()`, `.simulateCase2()`, `.simulateCase3_MultipleDebts()`, `.simulateCase4_ExactDebtElimination()`, `.simulateCase5_PrecisionTest()`, `.simulateCase7_SettlementOverpayment()`, `.simulateCase8_ZeroAmountExpense()`, `.simulateCase9_LargeGroup()`, `.store()`, `.storeSettlement()`, `.testCalculationScenarios()`, `.validateAllScenarios()`, `.validateImplementation()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 2`** (20 nodes): `GroupController.php`, `GroupInvitation.php`, `GroupInvitation`, `.group()`, `.invitedBy()`, `GroupController`, `.acceptInvitation()`, `.addMemberByEmail()`, `.categories()`, `.destroy()`, `.isGroupAdmin()`, `.isGroupCreator()`, `.isGroupMember()`, `.join()`, `.joinByQr()`, `.members()`, `.qrJoinCode()`, `.store()`, `.update()`, `.updateCategories()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 3`** (19 nodes): `BalanceService.php`, `BalanceService`, `.buildStatements()`, `.calculateBalanceForUserAfter()`, `.calculateBalanceForUserBefore()`, `.calculateExpenseImpact()`, `.calculateNetBalanceFromLedger()`, `.calculateSettlementImpact()`, `.calculateSnapshot()`, `.consolidateDebts()`, `.createStatementRecords()`, `.formatBalances()`, `.formatExpenseDescription()`, `.formatSettlementDescription()`, `.getPaymentSuggestions()`, `.initializeMatrix()`, `.processExpense()`, `.processSettlement()`, `.resolveExpenseParticipantIds()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 4`** (15 nodes): `StatementRecord.php`, `.apiStatementHistory()`, `.userStatementView()`, `StatementRecord`, `.boot()`, `.expense()`, `.generateReferenceNumber()`, `.getFormattedBalanceAfterAttribute()`, `.getFormattedBalanceChangeAttribute()`, `.group()`, `.scopeBetweenDates()`, `.scopeByType()`, `.scopeForUser()`, `.settlement()`, `.user()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 5`** (13 nodes): `Group.php`, `Group`, `.boot()`, `.casts()`, `.creator()`, `.defaultExpenseCategories()`, `.expenses()`, `.generateInviteCode()`, `.generateQrJoinToken()`, `.getRouteKeyName()`, `.members()`, `.settlements()`, `.statementRecords()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 6`** (11 nodes): `SettlementController.php`, `SettlementController`, `.__construct()`, `.destroy()`, `.index()`, `.isGroupOwner()`, `.sendSettlementNotifications()`, `.show()`, `.snapshotForRecipient()`, `.store()`, `.update()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 7`** (8 nodes): `User.php`, `User`, `.casts()`, `.expenses()`, `.getRouteKeyName()`, `.groups()`, `.settlementsGiven()`, `.settlementsReceived()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 8`** (8 nodes): `StatementController.php`, `StatementController`, `.buildFallbackFeed()`, `.buildFallbackFeedForUser()`, `.buildGroupSnapshots()`, `.__construct()`, `.index()`, `.transactionKey()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 9`** (7 nodes): `Settlement.php`, `Settlement`, `.boot()`, `.fromUser()`, `.getRouteKeyName()`, `.group()`, `.toUser()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 10`** (7 nodes): `GroupMemberController.php`, `GroupMemberController`, `.canManageMembers()`, `.deactivate()`, `.reactivate()`, `.remove()`, `.updateNotificationPreferences()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 11`** (6 nodes): `Expense.php`, `Expense`, `.boot()`, `.getRouteKeyName()`, `.group()`, `.paidByUser()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 12`** (5 nodes): `WalletSnapshot.php`, `WalletSnapshot`, `.expense()`, `.settlement()`, `.user()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 13`** (4 nodes): `BalanceState.php`, `BalanceState`, `.expense()`, `.settlement()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 14`** (4 nodes): `BalanceController.php`, `BalanceController`, `.__construct()`, `.snapshot()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 22`** (3 nodes): `EnsureGroupMember.php`, `EnsureGroupMember`, `.handle()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 23`** (3 nodes): `EnsureAdminAuthenticated.php`, `EnsureAdminAuthenticated`, `.handle()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `StatementRecord` connect `Community 4` to `Community 0`, `Community 1`, `Community 3`, `Community 6`?**
  _High betweenness centrality (0.258) - this node is a cross-community bridge._
- **Why does `ApiPayload` connect `Community 0` to `Community 8`, `Community 2`, `Community 6`?**
  _High betweenness centrality (0.228) - this node is a cross-community bridge._
- **Why does `ExpenseController` connect `Community 1` to `Community 4`?**
  _High betweenness centrality (0.112) - this node is a cross-community bridge._
- **Are the 25 inferred relationships involving `ApiPayload` (e.g. with `.index()` and `.store()`) actually correct?**
  _`ApiPayload` has 25 INFERRED edges - model-reasoned connections that need verification._
- **Are the 12 inferred relationships involving `StatementRecord` (e.g. with `.createStatementRecords()` and `.createDetailedExpenseStatement()`) actually correct?**
  _`StatementRecord` has 12 INFERRED edges - model-reasoned connections that need verification._
- **Should `Community 0` be split into smaller, more focused modules?**
  _Cohesion score 0.08 - nodes in this community are weakly interconnected._
- **Should `Community 1` be split into smaller, more focused modules?**
  _Cohesion score 0.13 - nodes in this community are weakly interconnected._